<?php

declare(strict_types=1);

namespace UserManager\Form;

use Fig\Http\Message\RequestMethodInterface as Http;
use Htmx\Form\HtmxTrait;
use Laminas\Form;
use UserManager\Form\Fieldset\ResetPasswordFieldset;

final class ResetPassword extends Form\Form
{
    use HtmxTrait;

    public function __construct(
        $name = 'reset-password',
        $options = []
    ) {
        parent::__construct($name, $options);
    }

    public function init(): void
    {
        $this->setAttributes([
            'action' => $this->urlHelper->generate('Reset Password'),
            'method' => Http::METHOD_POST,
        ]);

        $this->add([
            'name' => 'acct-data',
            'type' => ResetPasswordFieldset::class,
            'options' => [
                'use_as_base_fieldset' => true,
            ],
        ])->add([
            'name'       => 'Register',
            'type'       => Form\Element\Submit::class,
            'attributes' => [
                'value' => 'Reset Password',
            ],
        ]);
    }
}
