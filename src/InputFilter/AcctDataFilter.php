<?php

declare(strict_types=1);

namespace UserManager\InputFilter;

use Laminas\Db\Adapter\AdapterAwareInterface;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\InputFilter\InputFilter;
use Laminas\Filter;
use Laminas\Validator;
use Webinertia\Filter\Uuid;
use Webinertia\Validator\PasswordRequirement;

class AcctDataFilter extends InputFilter implements AdapterAwareInterface
{
    use AdapterAwareTrait;

    public function __construct(
        private string $targetTable,
        private string $targetColumn,
        private array $passwordConfig
    ) {
    }

    public function init(): void
    {
        $this->add($this->getSpec());
    }

    public function getSpec(): array
    {
        return  [
            [
                'name'        => 'id',
                'allow_empty' => true,
                'filters'     => [
                    ['name' => Filter\ToInt::class],
                    ['name' => Filter\ToNull::class],
                ],
            ],
            [
                'name'        => 'verificationToken',
                'allow_empty' => true,
                'filters'     => [
                    ['name' => Filter\ToInt::class],
                    ['name' => Filter\ToNull::class],
                    ['name' => Uuid::class],
                ],
            ],
            [
                'name'     => 'firstName',
                'required' => true,
                'filters'  => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\StringLength::class,
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 50, // true, we may never see an email this length, but they are still valid
                        ],
                    ],
                ],
            ],
            [
                'name'     => 'lastName',
                'required' => true,
                'filters'  => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\StringLength::class,
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 50, // true, we may never see an email this length, but they are still valid
                        ],
                    ],
                ],
            ],
            [
                'name' => 'email',
                'required' => true,
                'filters'  => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\StringLength::class,
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 320, // true, we may never see an email this length, but they are still valid
                        ],
                    ],
                    // @see EmailAddress for $options
                    ['name' => Validator\EmailAddress::class],
                    [
                        'name'    => Validator\Db\NoRecordExists::class,
                        'options' => [
                            'table'     => $this->targetTable,
                            'field'     => $this->targetColumn,
                            'dbAdapter' => $this->adapter,
                            'messages'  => [
                                Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Email is already in use!!',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name'     => 'password',
                'required' => true,
                'filters'  => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\StringLength::class,
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ],
                    ],
                    [
                        'name' => PasswordRequirement::class,
                        'options' => $this->passwordConfig,
                    ],
                ],
            ],
            [
                'name'       => 'conf_password',
                'required'   => true,
                'filters'    => [
                    ['name' => Filter\StripTags::class],
                    ['name' => Filter\StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => Validator\StringLength::class,
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ],
                    ],
                    [
                        'name'    => Validator\Identical::class,
                        'options' => [
                            'token'    => 'password',
                            'messages' => [
                                Validator\Identical::NOT_SAME => 'Passwords are not the same',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
