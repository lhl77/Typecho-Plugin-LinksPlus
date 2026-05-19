<?php

interface Links_MailDriverInterface
{
    /**
     * @param string $to
     * @param string $subject
     * @param string $htmlBody
     * @param array<string,string> $options
     * @return array{ok:bool,error:string}
     */
    public function send($to, $subject, $htmlBody, array $options = array());
}
