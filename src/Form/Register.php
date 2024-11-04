<?php

declare(strict_types=1);

namespace UserManager\Form;

use Fig\Http\Message\RequestMethodInterface as Http;
use Htmx\Form\HtmxTrait;
use Laminas\Form;
use UserManager\Form\Fieldset\AcctDataFieldset;

class Register extends Form\Form
{
    use HtmxTrait;

    public function init(): void
    {
        $this->setAttributes([
            'action' => $this->urlHelper->generate('Register'),
            'method' => Http::METHOD_POST,
        ]);
        $this->add([
                'name'    => 'acct-data',
                'type'    => AcctDataFieldset::class,
                'options' => [
                    'use_as_base_fieldset' => true,
                ],
        ])->add([
            'name'       => 'Register',
            'type'       => Form\Element\Submit::class,
            'attributes' => [
                'value' => 'Register',
            ],
        ]);
    }
}
