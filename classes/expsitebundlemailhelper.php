<?php
class expSiteBundleMailHelper
{
    public function sendMail( $receivers, $subject, $template, $parameters = array(), $sender = null )
    {
        $senderAddress = $this->createSenderAddress( $sender );
        if ( $senderAddress === false )
            return false;

        $mail = new eZMail();
        $mail->setSender( $senderAddress['email'], $senderAddress['name'] );
        $mail->setSubject( $subject );
        $mail->setContentType( 'text/html' );

        $this->addReceivers( $mail, $receivers );

        $body = $this->renderTemplate( $template, $parameters );
        if ( $body === false )
            return false;

        $mail->setBody( $body );

        return eZMailTransport::send( $mail );
    }

    protected function createSenderAddress( $sender )
    {
        if ( $sender !== null && $sender !== '' && !empty( $sender ) )
        {
            if ( is_string( $sender ) )
                return array( 'email' => $sender, 'name' => '' );

            if ( is_array( $sender ) && count( $sender ) === 1 )
            {
                $keys = array_keys( $sender );
                if ( !isset( $sender[0] ) )
                {
                    $email = $keys[0];
                    $name = $sender[$email];
                    return array( 'email' => $email, 'name' => $name );
                }

                return array( 'email' => $sender[0], 'name' => '' );
            }

            return false;
        }

        $siteIni = eZINI::instance( 'site.ini' );
        if ( $siteIni->hasVariable( 'MailSettings', 'EmailSender' ) && $siteIni->variable( 'MailSettings', 'EmailSender' ) !== '' )
            $email = $siteIni->variable( 'MailSettings', 'EmailSender' );
        elseif ( $siteIni->hasVariable( 'MailSettings', 'AdminEmail' ) && $siteIni->variable( 'MailSettings', 'AdminEmail' ) !== '' )
            $email = $siteIni->variable( 'MailSettings', 'AdminEmail' );

        $name = '';
        if ( !empty( $email ) && $siteIni->hasVariable( 'MailSettings', 'EmailSenderName' ) )
            $name = $siteIni->variable( 'MailSettings', 'EmailSenderName' );

        if ( !empty( $email ) )
            return array( 'email' => $email, 'name' => $name );

        return false;
    }

    protected function addReceivers( eZMail $mail, $receivers )
    {
        foreach ( (array)$receivers as $key => $value )
        {
            if ( is_string( $key ) )
            {
                $mail->setReceiver( $key, $value );
            }
            elseif ( is_array( $value ) && count( $value ) === 1 )
            {
                $keys = array_keys( $value );
                $email = $keys[0];
                $name = $value[$email];
                $mail->setReceiver( $email, $name );
            }
            else
            {
                $mail->setReceiver( $value );
            }
        }
    }

    protected function renderTemplate( $template, $parameters )
    {
        $tpl = eZTemplate::factory();
        if ( is_array( $parameters ) )
        {
            foreach ( $parameters as $key => $value )
            {
                $tpl->setVariable( $key, $value );
            }
        }

        return $tpl->fetch( $template );
    }
}
