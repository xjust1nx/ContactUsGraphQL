<?php

namespace Codilar\ContactUsGraphQL\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

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
class SendMail implements ResolverInterface
{
    /**
     * @var Helper
     */
    private $dataHelper;

    /**
     * SendMail constructor.
     * @param Helper $dataHelper
     */
    public function __construct(
        Helper $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value|mixed|void
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['name']) || empty($args['email']) || empty($args['phone']) || empty($args['message'])) {
            throw new GraphQlInputException(__('All fields must be specified'));
        }
        $name = $args['name'];
        $email = $args['email'];
        $phone = $args['phone'];
        $message = $args['message'];
        $successMessage = $this->dataHelper->contactUs(
            $name,
            $email,
            $phone,
            $message
        );
        return $successMessage;
    }
}
