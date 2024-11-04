<?php

declare(strict_types=1);

namespace UserManager\Form\Fieldset;

use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use User\Form\Element\RoleSelect;
use User\Model\Roles;

final class RoleFieldset extends Fieldset implements InputFilterProviderInterface
{
    /** @var Roles $roleModel */
    protected $roleModel;
    /**
     * @return void
     * @throws InvalidArgumentException
     */
    public function __construct(Roles $roleModel)
    {
        $this->roleModel = $roleModel;
        parent::__construct('role-data');
    }

    public function init(): void
    {
        $this->add([
            'type'    => RoleSelect::class,
            'options' => [
                'label' => 'Assign Group?',
            ],
        ]);
    }

    public function getInputFilterSpecification(): array
    {
        return [];
    }
}
