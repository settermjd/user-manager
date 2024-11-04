<?php

declare(strict_types=1);

namespace UserManager\Form\Fieldset;

use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Hydrator\ArraySerializableHydrator;
use UserManager\InputFilter\AcctDataFilter;
use UserManager\User\UserEntity;

final class AcctDataFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function init(): void
    {
        $this->setHydrator(new ArraySerializableHydrator());
        $this->setObject(new UserEntity());

        $this->add([
            'name' => 'id',
            'type' => Element\Hidden::class,
        ])->add([
            'name' => 'verificationToken',
            'type' => Element\Hidden::class,
        ])->add([
            'name'       => 'firstName',
            'type'       => Element\Text::class,
            'attributes' => [
                'placeholder' => 'First Name',
            ],
        ])->add([
            'name'       => 'lastName',
            'type'       => Element\Text::class,
            'attributes' => [
                'placeholder' => 'Last Name',
            ],
        ])->add([
            'name'       => 'email',
            'type'       => Element\Text::class,
            'attributes' => [
                'placeholder' => 'Email',
            ],
        ])->add([
            'name'       => 'password',
            'type'       => Element\Password::class,
            'attributes' => [
                'placeholder' => 'Password',
            ],
        ])->add([
            'name'       => 'conf_password',
            'type'       => Element\Password::class,
            'attributes' => [
                'placeholder' => 'Confirm Password',
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        $manager = $this->getFormFactory()->getInputFilterFactory()->getInputFilterManager();
        /** @var AcctDataFilter */
        $filter  = $manager->get(AcctDataFilter::class);
        return $filter->getSpec();
    }
}
