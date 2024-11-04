<?php

declare(strict_types=1);

namespace UserManager\Form;

use Fig\Http\Message\RequestMethodInterface as Http;
use Htmx\Form\HtmxTrait;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use UserManager\Form\Fieldset;

final class ChangePassword extends Form
{
    use HtmxTrait;

    public function __construct($name = 'change-password', $options = [])
    {
        parent::__construct($name, $options);
    }

    public function init(): void
    {
        $options = $this->getOptions();
        $this->setAttributes([
            'action' => $this->urlHelper->generate(
                routeName: 'Change Password',
                //options: ['reuse_result_params' => false]
            ),
            'method' => Http::METHOD_POST,
        ]);
        $this->add([
            'name' => 'acct-data',
            'type' => Fieldset\ChangePasswordFieldset::class,
            'options' => [
                'use_as_base_fieldset' => true,
                'password_options'     => $options['password_options']
            ]
        ]);
        $this->add([
            'name' => 'Submit',
            'type' => Submit::class,
            'attributes' => [
                'value' => 'Submit',
            ]
        ]);
    }
}
