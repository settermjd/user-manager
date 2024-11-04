<?php

declare(strict_types=1);

namespace UserManager\Form\Fieldset;

use App\Form\FormInterface;
use Laminas\Filter\HtmlEntities;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToInt;
use Laminas\Filter\UriNormalize;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\Uri;

final class SocialMediaFieldset extends Fieldset implements InputFilterProviderInterface
{
    /** @var array<mixed> $appSettings */
    protected $appSettings;
    public function __construct(?string $name = 'social-media', ?array $options = [], ?array $appSettings = [])
    {
        if ($options === [] || $options === null) {
            $options = [
                'mode' => FormInterface::EDIT_MODE,
            ];
        }
        $this->appSettings = $appSettings;
        parent::__construct($name, $options);
    }

    public function init(): void
    {
        parent::init();
        $this->add([
            'name' => 'id',
            'type' => Hidden::class,
        ])->add([
            'name' => 'userName',
            'type' => Hidden::class,
        ])->add([
            'name'    => 'facebook',
            'type'    => Text::class,
            'options' => [
                'label' => 'Facebook',
            ],
        ])->add([
            'name'    => 'twitter',
            'type'    => Text::class,
            'options' => [
                'label' => 'Twitter',
            ],
        ])->add([
            'name'    => 'instagram',
            'type'    => Text::class,
            'options' => [
                'label' => 'Instagram',
            ],
        ])->add([
            'name'    => 'slack',
            'type'    => Text::class,
            'options' => [
                'label' => 'Youtube',
            ],
        ]);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'id'       => [
                'filters' => [
                    ['name' => ToInt::class],
                ],
            ],
            'facebook' => [
                'filters'    => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class],
                    ['name' => HtmlEntities::class],
                    [
                        'name'    => UriNormalize::class,
                        'options' => [
                            'enforcedScheme' => 'https',
                        ],
                    ],
                ],
                'validators' => [
                    [
                        'name'    => Uri::class,
                        'options' => [
                            'allowRelative' => true,
                            'allowAbsolute' => true,
                        ],
                    ],
                ],
            ],
        ];
    }
}
