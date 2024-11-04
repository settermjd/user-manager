<?php

declare(strict_types=1);

namespace UserManager\Form\Fieldset;

use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Password;
use Laminas\Validator\StringLength;
use Webinertia\Validator\DbStoredPassword;

final class ChangePasswordFieldset extends PasswordFieldset
{
    use AdapterAwareTrait;

    private string $tableName;
    private string $columnName;
    private array  $passwordOptions;
    private bool   $hasValidToken = false;

    public function __construct($name = 'acct-data', $options = [])
    {
        parent::__construct($name, $options);
    }

    public function init(): void
    {
        parent::init();

        $this->add([
            'name' => 'id',
            'type' => Hidden::class,
        ]);
        $this->add([
            'name' => 'isTokenReset',
            'type' => Hidden::class,
        ]);
        $this->add(
            [
                'name' => 'current_password',
                'type' => Password::class,
                'attributes' => [
                    'placeholder' => 'Current Password',
                ],
            ],
            ['priority' => 1]
        );
    }

    public function getInputFilterSpecification(): array
    {
        $options = $this->getOptions();
        $spec    = parent::getInputFilterSpecification();
        $spec[] = [
            'name'       => 'current_password',
            'required'   => false,
            'allow_empty' => true,
            'filters'    => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name'    => DbStoredPassword::class,
                    'options' => [
                        'table'    => 'users',
                        'pkColumn' => 'id',
                        'pkValue'  => $options['userId'],
                        'password_column' => 'password',
                    ],
                ],
            ],
        ];
        return $spec;
    }
}
