<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

use App\Entity\Lesson;

class LessonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add(
                'questions',
                CollectionType::class,
                [
                    'entry_type' => QuestionType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'label' => 'Questions',
                    'by_reference' => false,
                ]
            )
            ->add(
                'translations',
                CollectionType::class,
                [
                    'entry_type' => TranslationType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'label' => 'Traductions',
                    'by_reference' => false,
                ]
            )
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([ 'data_class' => Lesson::class]);
    }
}

