# Production NGINX hardening (AlmaLinux/RHEL)

The live host uses `/etc/nginx/conf.d/yii2.conf` (root-owned), not `vagrant/nginx/app.conf`.

```bash
sudo install -m 644 deploy/nginx/security-headers.conf /etc/nginx/snippets-security-headers.conf
```

In `yii2.conf`, replace the three `add_header` lines in `server {}` with
`include /etc/nginx/snippets-security-headers.conf;` and add the same include inside the
static-file `location ~* \.(jpg|...)$` block (a location with its own `add_header`
drops all inherited headers). Add `server_tokens off;` in `http {}` of `nginx.conf`, then:

```bash
sudo nginx -t && sudo systemctl reload nginx
sudo dnf upgrade --refresh nginx php-fpm kernel   # RHEL backports fixes without bumping 1.26.3
```

Add `Strict-Transport-Security` only on the HTTPS edge (e.g. Cloudflare/cloudflared), not on this HTTP listener.
