# Great DANE for Webmail

Goals:
1. Retrieve and install certificates for senders of incoming emails
2. Retrieve and install certificates for recipients of outgoing emails

## Horde IMP

### Automatic Certificate Retrieval

`lib/Crypt/Smime.php`

- `IMP_Crypt_Smime` class
- `addPublicKey(...)` method
- `getPublicKey(email)` method

`getPublicKey` patched to query MockEngine for keys for email address. If keys are retrieved, they are added by calling `addPublicKey`. This ensures **all** attempts to send S/MIME messages check for DANE certificates.


### Automatic Encryption

`lib/Compose.php`:

- `buildAndSendMessage()` method of `IMP_Compose` class
- `$encrypt = empty($opts['encrypt']) ? 0 : $opts['encrypt'];`, one of:
    - `IMP_Crypt_Pgp::ENCRYPT`
    - `IMP_Crypt_Pgp::SIGNENC`
    - `IMP_Crypt_Smime::ENCRYPT`
    - `IMP_Crypt_Smime::SIGNENC`
- if `$encrypt` is `Smime...`, create and encrypt each MIME message individually!

## TODO

- Encryption enabled:
    - When message is encrypted certificates are automatically retrieved
    - Horde will complain if no certificate available for encryption

- Encryption disabled:
    - Automatically retrieve certificates
    - Attempt to encrypt...?
    - What if we only have certificates for *some*, but not all, recipients?
      Since Horde normally only encrypts when it is enabled (either globally, or per message),
      it complains if it doesn't have keys for *all* recipients.

