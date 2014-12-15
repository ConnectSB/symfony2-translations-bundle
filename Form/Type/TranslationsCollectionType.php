<?php
/**
 * Created by PhpStorm.
 * User: joey
 * Date: 6/19/14
 * Time: 4:05 PM
 */

namespace ConnectSB\TranslationBundle\Form\Type;

use ConnectSB\TranslationBundle\Service\DatabaseTranslationService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TranslationsCollectionType
 *
 * @package ConnectSB\TranslationBundle\Form\
 */
class TranslationsCollectionType extends AbstractType
{
    private $databaseTranslationService;

    public function __construct(DatabaseTranslationService $databaseTranslationService)
    {
        $this->databaseTranslationService = $databaseTranslationService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('translationKeys', 'collection', array(
                'label' => false,
                'type' => new TranslationKeyType(
                    $this->databaseTranslationService->getTranslationKeyEntity(),
                    $this->databaseTranslationService->getTranslationValueEntity()
                ),
                'allow_add' => false,
                'allow_delete' => false,
            ))
            ->add('save', 'submit');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'cascade_validation' => true
        ));
    }

    public function getName()
    {
        return 'database_translations';
    }
}
