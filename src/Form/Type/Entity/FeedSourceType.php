<?php

declare(strict_types=1);

namespace App\Form\Type\Entity;

use App\Entity\FeedSource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType; // Para cargar archivos
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\DataTransformer\Base64ToImageTransformer;


class FeedSourceType extends AbstractType
{
    public function __construct(
        private readonly Base64ToImageTransformer $base64ToImageTransformer,
        /*private readonly FeatureChecker $featureChecker,
        private readonly CollectionRepository $collectionRepository,
        private readonly TemplateRepository $templateRepository,
        private readonly DatumRepository $datumRepository*/
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'label' => 'Feed Name',
                'attr' => ['maxlength' => 255],
            ])
            ->add('url', UrlType::class, [
                'required' => true,
                'label' => 'Feed URL',
                'attr' => ['maxlength' => 255],
            ])
            ->add(
                $builder->create('file', TextType::class, [
                    'required' => false,
                    'label' => false,
                    'model_transformer' => $this->base64ToImageTransformer,
                ])
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FeedSource::class,
        ]);
    }
}
