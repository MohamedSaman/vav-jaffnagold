# PHP Critical Security Update Guide (CVE-2026-109618 & CVE-2026-109619)

> **Severity:** Critical (CVSS 9.8)  
> **Vulnerability:** Unauthenticated Remote Code Execution (RCE) via `session.upload_progress`  
> **Affected Versions:** PHP 8.1.x, 8.2.x (< 8.2.35), 8.3.x (< 8.3.36), 8.4.x (< 8.4.27), 8.5.x (< 8.5.12)  
> **Target Servers:** Any Linux / Production server running PHP

---

## ⚡ Option 1: Immediate 1-Minute Fix (Neutralizes Exploit Instantly)

Because upstream OS mirrors (Ubuntu, Debian, RHEL) can take 24–48 hours to compile and distribute newly published PHP binaries, disabling `session.upload_progress` immediately blocks the remote code execution vector.

### For Ubuntu / Debian Servers:

Detect your installed PHP version and apply the override file:

```bash
# 1. Detect PHP version (e.g., 8.2, 8.3, 8.5)
PHP_VER=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "Detected PHP version: $PHP_VER"

# 2. Add security drop-in configuration to both CLI and FPM
mkdir -p /etc/php/$PHP_VER/cli/conf.d /etc/php/$PHP_VER/fpm/conf.d
echo "session.upload_progress.enabled = Off" > /etc/php/$PHP_VER/cli/conf.d/99-security.ini
echo "session.upload_progress.enabled = Off" > /etc/php/$PHP_VER/fpm/conf.d/99-security.ini

# 3. Restart PHP-FPM and Web Server
systemctl restart php$PHP_VER-fpm
systemctl restart nginx 2>/dev/null || systemctl restart apache2 2>/dev/null

# 4. Verify (MUST show Off => Off)
php -i | grep "session.upload_progress.enabled"
```

---

### For CentOS / AlmaLinux / Rocky Linux / RHEL:

```bash
# 1. Add override configuration to php.d
echo "session.upload_progress.enabled = Off" > /etc/php.d/99-security.ini

# 2. Restart PHP-FPM and Web Server
systemctl restart php-fpm
systemctl restart nginx 2>/dev/null || systemctl restart httpd 2>/dev/null

# 3. Verify
php -i | grep "session.upload_progress.enabled"
```

---

### For cPanel / WHM Servers (ea-php):

Run for each installed EasyApache PHP version (e.g. ea-php81, ea-php82, ea-php83):

```bash
for dir in /opt/cpanel/ea-php*/root/etc/php.d; do
    if [ -d "$dir" ]; then
        echo "session.upload_progress.enabled = Off" > "$dir/99-security.ini"
        echo "Secured $dir"
    fi
done

# Restart services
/scripts/restartsrv_apache
/scripts/restartsrv_cpanel_php_fpm
```

---

### For Docker Containers:

Add this line to your `Dockerfile` or mount it as a volume:

```dockerfile
RUN echo "session.upload_progress.enabled = Off" > /usr/local/etc/php/conf.d/99-security.ini
```

---

## 📦 Option 2: Full Package Upgrade

Once your Linux distribution's repository mirrors receive the compiled security patch, upgrade the system packages.

### Ubuntu / Debian:

```bash
# Update repository package metadata
sudo apt update

# Upgrade only PHP packages to prevent unintended major OS upgrades
PHP_VER=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
sudo apt --only-upgrade install -y "php${PHP_VER}*"

# Restart web services
sudo systemctl restart php${PHP_VER}-fpm
sudo systemctl restart nginx 2>/dev/null || sudo systemctl restart apache2 2>/dev/null

# Verify version
php -v
```

> **Note for Ondřej Surý PPA users (Ubuntu/Debian):**  
> If using `ppa:ondrej/php`, updates usually arrive within a few hours:
> ```bash
> sudo apt update
> sudo apt --only-upgrade install -y php*
> ```

### AlmaLinux / Rocky Linux / RHEL:

```bash
sudo dnf check-update
sudo dnf upgrade -y "php*"
sudo systemctl restart php-fpm
sudo systemctl restart nginx 2>/dev/null || sudo systemctl restart httpd 2>/dev/null
php -v
```

---

## ✅ Post-Update Verification Checklist

Run this quick test on the server to ensure full security:

```bash
php -i | grep "session.upload_progress"
```

**Expected output:**
```text
session.upload_progress.cleanup => On => On
session.upload_progress.enabled => Off => Off
```

- When `session.upload_progress.enabled` is `Off`, the vulnerable code path in PHP core is inactive and attackers cannot trigger memory corruption or unauthenticated RCE.
- Normal Laravel / web application file uploads (`$request->file(...)`) continue to work completely unaffected because Laravel handles file uploads independently.
