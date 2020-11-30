<?php

namespace Codilar\ContactUsGraphQL\Model\Resolver;

use Codilar\ContactUsGraphQL\Logger\Logger;
use Exception;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\DataObject;
use Magento\Framework\Message\ManagerInterface;

/**
 *
 * @description Magento Module for Contact Us Form using GraphQL
 * @author   Codilar Team Player <sooraj.pr@codilar.com>
 * @license  Open Source
 * @link     https://www.codilar.com
 * @copyright Copyright © 2020 Codilar Technologies Pvt. Ltd.. All rights reserved
 *
 * Magento Module for Contact Us Form using GraphQL
 */
class Helper
{
    /**
     * @var MailInterface
     */
    private $mail;
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;
    /**
     * @var FormKey
     */
    private $formKey;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * Helper constructor.
     * @param MailInterface $mail
     * @param DataPersistorInterface $dataPersistor
     * @param Logger $logger
     * @param FormKey $formKey
     */
    public function __construct(
        MailInterface $mail,
        DataPersistorInterface $dataPersistor,
        Logger $logger,
        FormKey $formKey
    ) {
        $this->mail = $mail;
        $this->dataPersistor = $dataPersistor;
        $this->formKey = $formKey;
        $this->logger = $logger;
    }

    public function contactUs($name, $email, $phone, $message)
    {
        $thanks_message = [];
        try {
            $this->sendEmail($name, $email, $phone, $message);
            $thanks_message['successMessage'] = 'Thanks for contacting us. We will get back to you soon';
        } catch (Exception $e) {
            $thanks_message['successMessage'] = 'Error sending the mail. Please try again.';
            $this->logger->addWarning($e->getMessage());
        }
        return $thanks_message;
    }

    /**
     * @param $name
     * @param $email
     * @param $phone
     * @param $message
     * @throws Exception
     */
    public function sendEmail($name, $email, $phone, $message)
    {
        $form_data = [];
        $form_data['name'] = $name;
        $form_data['email'] = $email;
        $form_data['telephone'] = $phone;
        $form_data['comment'] = $message;
        $form_data['hideit'] = "";
        $form_data['form_key'] = $this->formKey->getFormKey();
        $this->mail->send($email, ['data' => new DataObject($form_data)]);
    }
}
