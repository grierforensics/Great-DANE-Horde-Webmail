# Great DANE for Webmail

Goals:

1. Retrieve and install certificates for senders of incoming emails
2. Retrieve and install certificates for recipients of outgoing emails

## Horde IMP

Install the following packages using PEAR, as specified in the linked INSTALL instructions:

- Horde 5.2.12 (stable) https://www.horde.org/apps/horde/docs/INSTALL#installing-with-pear
- IMP 6.2.16 (stable) https://www.horde.org/apps/imp/docs/INSTALL#installing-with-pear
- Turba 4.2.16 (stable) https://www.horde.org/apps/turba/docs/INSTALL#installing-with-pear

Tips:

- Follow the installation instructions carefully
- Choose an installation location using `horde_role`, e.g. `/var/www/horde` (covered in installation instructions)
- Use Apache, MySQL backend (mariadb on CentOS 7).
- Create `horde` database, and user with all privileges.
- Use `pear install -a -B` to get optional dependencies.
- After installing, copy the horde.conf Apache config to `/etc/httpd/conf.d/horde.conf`

Post-installation steps:

- Configure database access using database and user created earlier
- Run the following to create the database tables:

    - `/usr/bin/horde_db_migrate`
    - `/usr/bin/horde_db_migrate imp`
    - `/usr/bin/horde_db_migrate turba` (2-3x for Turba tables to be properly created)

- Create a "real" Administrator by adding a user
- Configure logging to write to `/var/log/horde/horde.log`
- Ensure all of `/var/www/horde` and `/var/log/horde` are owned by `apache` user
- Configure OpenSSL (`cafile` = `/etc/ssl/certs`, `path` = `/usr/bin/openssl`)
- Configure an IMAP server for IMP in `/var/www/horde/imp/config/backends.local.php` using the included example

Preferences:

- Set your Local and Time (Global)
- Set your user identity (name and email address) under Personal Information
- Indicate whether attachments exist under Mailbox Display (Show Advanced Preferences)
- Enable S/MIME functionality under S/MIME
- Upload personal certificate under S/MIME
- (Optionally) ensure S/MIME signed messages are automatically verified under S/MIME
- (Optionally) choose to default to signed and/or encrypted messages under Composition
- (Optionally) add color code to Encrypted/Signed messages under Flags
- Configure Great DANE Engine address under Great DANE
- (Optionally) enable opportunistic S/MIME encryption under Great DANE

### Automatic Certificate Retrieval

Every time a public key/cert is used in IMP, the `IMP_Crypt_Smime::getPublicKey` function is called. This, in turn, calls an IMP Hook called `'smime_key'`, which we've implemented to use the Great DANE Engine. This hook is found in `config/hooks.php`. Our implementation attempts to retrieve and store all certificates for the given recipient email address, returning only the first one for use in encrypting an outgoing message.

### Automatic Encryption

All outgoing messages are constructed in `IMP_Compose::buildAndSendMessage`. Unfortunately there aren't any hooks called before messages are encrypted, so `lib/Compose.php` is patched to optionally perform *Opportunistic Encryption*. This code attempts to retrieve a public cert for all intended recipients and, if successful, automatically enables S/MIME encryption (and signing).

### Preferences

Opportunistic Encryption and the Great DANE Engine's HTTP address are configurable in the Great DANE preference pane. The pane and preferences are specified in `config/prefs.local.php`.
