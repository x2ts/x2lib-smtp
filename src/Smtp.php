<?php
/**
 * Created by IntelliJ IDEA.
 * User: rek
 * Date: 2017/4/20
 * Time: PM2:48
 */

namespace x2lib\smtp;

use PHPMailer;
use x2ts\IComponent;
use x2ts\TConfig;
use x2ts\TGetterSetter;
use x2ts\Toolkit;


/**
 * Class Smtp
 *
 * @package x2lib\smtp
 * @property-read array $conf
 */
class Smtp extends PHPMailer implements IComponent {
    use TGetterSetter;
    use TConfig;

    protected static $_conf = [
        'charset'   => 'UTF-8',
        'host'      => 'smtp.example.com',
        'port'      => 465,
        'secure'    => 'ssl',
        'options'   => [
            'ssl' => [
                'verify_peer'       => true,
                'verify_depth'      => 3,
                'allow_self_signed' => false,
                'peer_name'         => 'smtp.example.com',
            ],
        ],
        'auth'      => 'true',
        'username'  => 'foobar@example.com',
        'password'  => 'f008ar',
        'fromName'  => 'Foo Bar',
        'fromAddr'  => 'foobar@example.com',
        'autoClear' => true,
    ];

    public function init() {
        Toolkit::trace($this->conf);
        $this->isSMTP();
        $this->CharSet = $this->conf['charset'];
        $this->Host = $this->conf['host'];
        $this->Port = $this->conf['port'];
        $this->SMTPSecure = $this->conf['secure'];
        $this->SMTPOptions = $this->conf['options'];
        $this->SMTPAuth = $this->conf['auth'];
        $this->Username = $this->conf['username'];
        $this->Password = $this->conf['password'];
        if ($this->conf['fromAddr']) {
            if ($this->conf['fromName']) {
                $this->setFrom($this->conf['fromAddr'], $this->conf['fromName']);
            } else {
                $this->setFrom($this->conf['fromAddr']);
            }
        }
        if (X_DEBUG) {
            $this->SMTPDebug = 2;
            $this->Debugoutput = function ($str) {
                Toolkit::trace($str);
            };
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param string      $subject
     * @param string      $body
     * @param string      $to
     * @param string|null $cc
     * @param string|null $bcc
     *
     * @return bool
     */
    public function sendmail(string $subject, string $body, string $to, string $cc = null, string $bcc = null) {
        Toolkit::trace('sending mail');
        $this->Subject = $subject;
        $this->msgHTML($body);
        $addresses = $this->parseAddresses($to);
        foreach ($addresses as $address) {
            $this->addAddress($address['address'], $address['name']);
        }
        if ($cc) {
            $addresses = $this->parseAddresses($cc);
            foreach ($addresses as $address) {
                $this->addCC($address['address'], $address['name']);
            }
        }
        if ($bcc) {
            $addresses = $this->parseAddresses($bcc);
            foreach ($addresses as $address) {
                $this->addBCC($address['address'], $address['name']);
            }
        }

        $result = $this->send();
        if ($result && $this->conf['autoClear']) {
            $this->clearAllRecipients();
            $this->clearAttachments();
            $this->clearCustomHeaders();
            $this->clearReplyTos();
            $this->Subject = '';
            $this->Body = '';
            $this->AltBody = '';
        }
        return $result;
    }
}
