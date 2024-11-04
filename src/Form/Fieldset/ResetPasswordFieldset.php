<?php

declare(strict_types=1);

namespace UserManager\Form\Fieldset;

use Laminas\Db\Adapter\AdapterAwareInterface;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Filter;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Hydrator\ArraySerializableHydrator;
use Laminas\Validator;
use UserManager\User\UserEntity;
use Webinertia\Filter\Uuid;

final class ResetPasswordFieldset extends Fieldset implements AdapterAwareInterface, InputFilterProviderInterface
{
    use AdapterAwareTrait;

    public function __construct(
        private string $targetTable,
        private string $targetColumn,
        $name = 'acct-data',
        $options = []
    ) {
        parent::__construct($name, $options);
    }

    public function init(): void
    {
        $this->setHydrator(new ArraySerializableHydrator());
        $this->setObject(new UserEntity());

        $this->add([
            'name' => 'passwordResetToken',
            'type' => Element\Hidden::class,
        ])->add([
            'name'    => 'email',
            'type'    => Element\Email::class,
            'attributes' => [
                'placeholder' => 'Email',
            ],
        ]);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            [
                'name'        => 'passwordResetToken',
                'allow_empty' => true,
                'filters'     => [
                    ['name' => Filter\ToInt::class],
                    ['name' => Filter\ToNull::class],
                    ['name' => Uuid::class],
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
                        'name'    => Validator\Db\RecordExists::class,
                        'options' => [
                            'table'     => $this->targetTable,
                            'field'     => $this->targetColumn,
                            'dbAdapter' => $this->adapter,
                            'messages'  => [
                                Validator\Db\RecordExists::ERROR_NO_RECORD_FOUND => 'Email not found!!',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
