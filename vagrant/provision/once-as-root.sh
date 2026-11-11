#!/usr/bin/env bash
set -euo pipefail

#== Import script args ==

timezone=${1:?Timezone is required}

#== Bash helpers ==

function info {
  echo " "
  echo "--> $1"
  echo " "
}

#== Provision script ==

info "Provision-script user: `whoami`"

export DEBIAN_FRONTEND=noninteractive

info "Configure timezone"
timedatectl set-timezone "$timezone" --no-ask-password

info "Update OS software"
apt-get update
apt-get upgrade -y

info "Add the official NGINX stable repository"
apt-get install -y ca-certificates curl gnupg lsb-release ubuntu-keyring
install -d -m 0755 /usr/share/keyrings
curl -fsSL https://nginx.org/keys/nginx_signing.key -o /tmp/nginx_signing.key
nginx_key_fingerprint=$(gpg --show-keys --with-colons /tmp/nginx_signing.key | awk -F: '$1 == "fpr" { print $10; exit }')
if [ "$nginx_key_fingerprint" != "573BFD6B3D8FBC641079A6ABABF5BD827BD9BF62" ]; then
  echo "Unexpected NGINX signing key fingerprint: $nginx_key_fingerprint" >&2
  exit 1
fi
gpg --dearmor --output /usr/share/keyrings/nginx-archive-keyring.gpg /tmp/nginx_signing.key
rm -f /tmp/nginx_signing.key
. /etc/os-release
echo "deb [signed-by=/usr/share/keyrings/nginx-archive-keyring.gpg] https://nginx.org/packages/ubuntu ${VERSION_CODENAME} nginx" \
  > /etc/apt/sources.list.d/nginx.list
cat > /etc/apt/preferences.d/99nginx <<'EOF'
Package: *
Pin: origin nginx.org
Pin: release o=nginx
Pin-Priority: 900
EOF

info "Install additional software"
apt-get update
apt-get install -y php8.3-curl php8.3-cli php8.3-intl php8.3-mysql php8.3-gd php8.3-fpm php8.3-mbstring php8.3-xml php-xdebug unzip nginx mysql-server

info "Configure PHP-FPM"
sed -i 's/^user = www-data/user = vagrant/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/^group = www-data/group = vagrant/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/^;*listen.owner = www-data/listen.owner = vagrant/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/^;*listen.group = www-data/listen.group = vagrant/' /etc/php/8.3/fpm/pool.d/www.conf
cat << EOF > /etc/php/8.3/mods-available/xdebug.ini
xdebug.mode=debug
xdebug.discover_client_host=1
xdebug.client_port=9000
xdebug.start_with_request=yes
EOF
phpenmod xdebug
echo "Done!"

info "Configure NGINX"
sed -i 's/^user .*/user vagrant;/' /etc/nginx/nginx.conf
echo "Done!"

info "Enabling site configuration"
ln -sfn /app/vagrant/nginx/app.conf /etc/nginx/conf.d/app.conf
echo "Done!"

info "Initailize databases for MySQL"
mysql -uroot <<< "CREATE DATABASE IF NOT EXISTS yii2advanced"
mysql -uroot <<< "CREATE DATABASE IF NOT EXISTS yii2advanced_test"
echo "Done!"

info "Install composer"
curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer