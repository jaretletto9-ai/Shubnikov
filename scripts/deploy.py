import json
import os
from pathlib import Path
import secrets
import shutil
import subprocess
import sys
import time

source = Path(sys.argv[1]).resolve()
target = Path('/var/www/shubnikov-web3')
data = Path('/var/lib/shubnikov-web3')
run = Path('/run/shubnikov-web3')
logs = Path('/var/log/shubnikov-web3')
def call(args, **kwargs):
    return subprocess.run(args, check=True, text=True, **kwargs)

if os.geteuid() != 0:
    raise SystemExit('Run deployment as root on the owned Kali lab only.')
for directory in (target, data, run, logs):
    directory.mkdir(parents=True, exist_ok=True)
if not (data/'mysql').exists():
    call(['mariadb-install-db','--user=mysql','--datadir='+str(data),'--auth-root-authentication-method=socket','--skip-test-db'], stdout=subprocess.DEVNULL)
fixtures = data/'fixtures'
fixtures.mkdir(exist_ok=True)
(fixtures/'passwd-demo.txt').write_text('student_demo:x:1001:1001:Шубников А.А.:/home/student_demo:/bin/false\n')
os.chmod(fixtures, 0o755)
os.chmod(fixtures/'passwd-demo.txt', 0o644)
call(['chown','-R','mysql:mysql',str(data)])
call(['chown','mysql:mysql',str(run)])
socket = run/'mariadb.sock'
if subprocess.run(['systemctl','is-active','--quiet','shubnikov-web3-db']).returncode != 0:
    call(['systemd-run','--unit=shubnikov-web3-db','--property=Restart=on-failure',
          '/usr/sbin/mariadbd','--no-defaults','--user=mysql','--datadir='+str(data),
          '--socket='+str(socket),'--port=3307','--bind-address=127.0.0.1',
          '--secure-file-priv='+str(fixtures),'--local-infile=0',
          '--pid-file='+str(run/'mariadb.pid'),'--log-error='+str(data/'error.log')])
for attempt in range(60):
    if subprocess.run(['mariadb-admin','--socket='+str(socket),'ping'], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL).returncode == 0:
        break
    time.sleep(1)
else:
    raise SystemExit('Isolated MariaDB failed to start.')

secrets_file = target/'lab-secrets.json'
credentials = json.loads(secrets_file.read_text()) if secrets_file.exists() else {'app':secrets.token_hex(20),'lab':secrets.token_hex(20)}
secrets_file.write_text(json.dumps(credentials))
os.chmod(secrets_file, 0o600)
sql = (source/'schema.sql').read_text()
sql += """
DROP USER IF EXISTS ''@'localhost';
DROP USER IF EXISTS ''@'%';
CREATE USER IF NOT EXISTS 'web3_app'@'127.0.0.1' IDENTIFIED BY '{app}';
CREATE USER IF NOT EXISTS 'web3_lab'@'127.0.0.1' IDENTIFIED BY '{lab}';
GRANT SELECT, INSERT, UPDATE, DELETE ON shubnikov_web3.* TO 'web3_app'@'127.0.0.1';
GRANT SELECT ON shubnikov_web3.demo_users TO 'web3_lab'@'127.0.0.1';
GRANT FILE ON *.* TO 'web3_lab'@'127.0.0.1';
FLUSH PRIVILEGES;
""".format(**credentials)
call(['mariadb','--socket='+str(socket),'-u','root'], input=sql)
shutil.copytree(source,target,dirs_exist_ok=True)
php_config = "<?php return ['user'=>'web3_app','password'=>'{app}','lab_user'=>'web3_lab','lab_password'=>'{lab}'];\n".format(**credentials)
(target/'config.php').write_text(php_config)
call(['chown','root:www-data',str(target/'config.php')])
os.chmod(target/'config.php',0o640)
for upload in (target/'public/uploads', target/'public/lab/uploads'):
    upload.mkdir(exist_ok=True)
    call(['chown','www-data:www-data',str(upload)])
    os.chmod(upload,0o750)
conf = Path('/etc/apache2/shubnikov-web3.conf')
conf.write_text("""
ServerRoot "/etc/apache2"
PidFile /run/shubnikov-web3/apache.pid
DefaultRuntimeDir /run/shubnikov-web3
ServerName localhost
Listen 127.0.0.1:8083
Listen 192.168.1.5:8083
IncludeOptional /etc/apache2/mods-enabled/*.load
IncludeOptional /etc/apache2/mods-enabled/*.conf
User www-data
Group www-data
ErrorLog /var/log/shubnikov-web3/error.log
LogLevel warn
CustomLog /var/log/shubnikov-web3/access.log combined
DirectoryIndex index.php
DocumentRoot /var/www/shubnikov-web3/public
<Directory />
 Require all denied
</Directory>
<Directory /var/www/shubnikov-web3/public>
 Options -Indexes
 AllowOverride None
 Require ip 127.0.0.1 192.168.1.254
</Directory>
<Directory /var/www/shubnikov-web3/public/uploads>
 php_admin_flag engine Off
 <FilesMatch "\\.(php|phtml|phar)$">
  Require all denied
 </FilesMatch>
</Directory>
php_admin_flag display_errors Off
php_admin_value error_log /var/log/shubnikov-web3/php-error.log
php_admin_value upload_max_filesize 2M
php_admin_value post_max_size 3M
""")
env = dict(os.environ, APACHE_RUN_DIR=str(run), APACHE_LOCK_DIR=str(run), APACHE_LOG_DIR=str(logs), APACHE_PID_FILE=str(run/'apache.pid'), APACHE_RUN_USER='www-data', APACHE_RUN_GROUP='www-data')
call(['apache2','-t','-f',str(conf)], env=env)
active = subprocess.run(['systemctl','is-active','--quiet','shubnikov-web3-apache']).returncode == 0
if active:
    call(['systemctl','stop','shubnikov-web3-apache'])
call(['systemd-run','--unit=shubnikov-web3-apache','--property=Restart=on-failure',
      *['--setenv='+key+'='+env[key] for key in ['APACHE_RUN_DIR','APACHE_LOCK_DIR','APACHE_LOG_DIR','APACHE_PID_FILE','APACHE_RUN_USER','APACHE_RUN_GROUP']],
      '/usr/sbin/apache2','-f',str(conf),'-DFOREGROUND'])
print('DEPLOYED: isolated Apache 8083 / MariaDB 3307 / /var/www/shubnikov-web3')
