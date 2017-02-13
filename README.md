# Great DANE for Horde Webmail

Great DANE for Horde Webmail consists of a plugin for [Horde IMP](https://www.horde.org/apps/imp/), a popular, open-source, web-based mail client.

The plugin retrieves and installs public S/MIME certificates from the Great DANE Engine for each recipient of outgoing emails.

## Install

Install Horde and IMP, preferably using PEAR, as specified in the linked instructions for each package.
Great DANE for Webmail has been tested on CentOS 7 using the following Horde packages:

- [Horde 5.2.13 (stable)](https://www.horde.org/apps/horde/docs/INSTALL#installing-with-pear)
- [IMP 6.2.17 (stable)](https://www.horde.org/apps/imp/docs/INSTALL#installing-with-pear)
- [Turba 4.2.18 (stable)](https://www.horde.org/apps/turba/docs/INSTALL#installing-with-pear)

To install Great DANE for Horde Webmail, you must copy two files from the project's source code into your Horde installation.
Assuming your Horde installation location is `/var/www/horde`, copy the following files from Great DANE to Horde:

- `config/hooks.php` -> `/var/www/horde/config/hooks.php`
- `config/prefs.local.php` -> `/var/www/horde/config/prefs.local.php`

## Configure

Navigate to Preferences -> Mail, then perform the following:

- Set your user identity (name and email address) under Personal Information
- Indicate whether attachments exist under Mailbox Display (Show Advanced Preferences)
- Enable S/MIME functionality under S/MIME
- Upload personal certificate under S/MIME
- Configure Great DANE Engine address under Great DANE
- (Optionally) enable opportunistic S/MIME encryption under Great DANE

## Overview

### Automatic Certificate Retrieval

Each time a public key/cert is used in IMP, the `IMP_Crypt_Smime::getPublicKey` function is called. This, in turn, calls an IMP Hook called `'smime_key'`, which we've implemented to use the Great DANE Engine. This hook is found in `config/hooks.php`. The hook attempts to retrieve and store all certificates for each recipient email address, returning only the first one for use in encrypting an outgoing message.

### Opportunistic Encryption

All outgoing messages are constructed in `IMP_Compose::buildAndSendMessage`. Unfortunately there aren't any hooks called before messages are encrypted, so `lib/Compose.php` is patched to optionally perform *Opportunistic Encryption*. This code attempts to retrieve a public cert for all intended recipients and, if successful, automatically enables S/MIME encryption (and signing).

Automatic S/MIME encryption should soon be added to Horde IMP. Follow [#12736](https://bugs.horde.org/ticket/12736) for more details.

To add Opportunistic Encryption to Horde Webmail, apply the `Compose.php.patch` patch in the `lib/` directory of this project:

```
$ patch `/var/www/horde/imp/lib/Compose.php lib/Compose.php.patch
```

### Preferences

Opportunistic Encryption and the Great DANE Engine's HTTP address are configurable in the Great DANE preference pane. The pane and preferences are defined in `config/prefs.local.php` and found under Preferences -> Mail, below the S/MIME preference pane.

## License

Dual-licensed under Apache License 2.0 and 3-Clause BSD License. See LICENSE.
