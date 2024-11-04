<?php

declare(strict_types=1);

namespace UserManager\Form\Fieldset;

use Laminas\Filter;
use Laminas\Form\Fieldset;
use Laminas\Form\Element;
use Laminas\Hydrator\ArraySerializableHydrator;
use Laminas\InputFilter\InputFilterProviderInterface;
use UserManager\User\UserEntity;
use Webinertia\Filter\Uuid;

final class ResendVerification extends Fieldset implements InputFilterProviderInterface
{
    public function init()
    {
        $this->setObject(new UserEntity());
        $this->setHydrator(new ArraySerializableHydrator());
        $this->setUseAsBaseFieldset(true);
        $this->add([
            'name' => 'id',
            'type' => Element\Hidden::class,
        ])->add([
            'name' => 'verificationToken',
            'type' => Element\Hidden::class,
        ]);
    }

    public function getInputFilterSpecification(): array
    {
        return [
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
        ];
    }
}
