<?php

class IMP_Hooks
{
    /**
     * Retrieves public S/MIME keys of message recipients using the Great DANE Engine.
     *
     * This hook is called first when searching for public certificates.
     * If retrieval fails, IMP falls back to searching the user's address book
     * for existing, stored public certificates.
     *
     * @param string $address  The email address of the recipient.
     *
     * @return string  The base64-encoded public S/MIME key that matches the email address.
     */
    public function smime_key($address)
    {
        global $injector;

        $http = $injector->getInstance('Horde_Http_Client');
        $smime = $injector->getInstance('IMP_Crypt_Smime');
        $engine = $GLOBALS['prefs']->getValue('gd_engine_addr');

        $resp = null;
        try {
            $resp = $http->get("$engine/$address/pem");
        } catch (Horde_Exception $e) {
            Horde::log('Great DANE: Failed to make HTTP request: ' . $e->getMessage(), 'ERROR');
            return null;
        }

        $code = $resp->code;
        if ($code != 200) {
            if ($code >= 500 && code < 600) {
                Horde::log("Great DANE: Server error (HTTP $code)", 'WARN');
            } else {
                Horde::log("Great DANE: No certificates found for $address (HTTP $code)", 'INFO');
            }
            return null;
        }

        /*
         *  The Great DANE Engine returns a response of the form:
         *  {
         *    "certificates": [
         *      {
         *        "data": string (encoded certificate),
         *        "ttl": integer (from DNS record),
         *        "dnssecValidated": boolean (whether DNSSEC validation was successful),
         *        "certificateUsage": integer (as per RFC6698 and RFC8162),
         *        "selector": integer (as per RFC6698 and RFC8162),
         *        "matchingType": integer (as per RFC6698 and RFC8162)
         *      }
         *    ]
         *  }
         *
         *  but we are only concerned with the certificate `data` field for now.
         */
        $body = $resp->getBody();
        $resp = json_decode($body, true);
        $pubKey = null;
        if (isset($resp['certificates'])) {
            foreach ($resp['certificates'] as $cert) {
                $key = $cert['data'];
                try {
                    $smime->addPublicKey($key);
                } catch (Turba_Exception $e) {
                    // We don't care if the key is already in our address book
                    continue;
                } catch (Horde_Crypt_Exception $e) {
                    Horde::log('Great DANE: Invalid public key: ' . $e->getMessage(), 'ERROR');
                    continue;
                }

                // Use the first fetched key
                if (!isset($pubKey)) {
                    $pubKey = $key;
                }

                Horde::log("Great DANE: Certificate added for $address", 'INFO');
            }
        }

        return $pubKey;
    }
}
