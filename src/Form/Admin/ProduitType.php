<?php

namespace App\Form\Admin;

use App\Entity\Produit;
use App\Entity\SousCategorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire.']),
                ],
                'attr' => ['placeholder' => 'Ex : Bureau en bois massif'],
            ])
            ->add('reference', TextType::class, [
                'label' => 'Référence',
                'constraints' => [
                    new NotBlank(['message' => 'La référence est obligatoire.']),
                ],
                'attr' => ['placeholder' => 'Ex : REF-001'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new NotBlank(['message' => 'La description est obligatoire.']),
                ],
                'attr' => ['rows' => 5, 'placeholder' => 'Décrivez le produit...'],
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix (GNF)',
                'scale' => 2,
                'constraints' => [
                    new NotBlank(['message' => 'Le prix est obligatoire.']),
                    new Positive(['message' => 'Le prix doit être positif.']),
                ],
                'attr' => ['placeholder' => 'Ex : 150000'],
            ])
            ->add('quantite_stock', IntegerType::class, [
                'label' => 'Quantité en stock',
                'constraints' => [
                    new NotBlank(['message' => 'La quantité est obligatoire.']),
                    new PositiveOrZero(['message' => 'La quantité doit être positive ou nulle.']),
                ],
                'attr' => ['placeholder' => 'Ex : 10'],
            ])
            ->add('marque', TextType::class, [
                'label' => 'Marque',
                'required' => false,
                'attr' => ['placeholder' => 'Ex : Samsung'],
            ])
            ->add('sousCategorie', EntityType::class, [
                'class' => SousCategorie::class,
                'choice_label' => 'nom',
                'label' => 'Sous-catégorie',
                'placeholder' => '-- Sélectionner une sous-catégorie --',
                'constraints' => [
                    new NotNull(['message' => 'La sous-catégorie est obligatoire.']),
                ],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image du produit',
                'mapped' => false,
                'required' => !$isEdit,
                'constraints' => array_filter([
                    !$isEdit ? new NotBlank(['message' => 'L\'image est obligatoire.']) : null,
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG ou WEBP).',
                    ]),
                ]),
            ])
            ->add('estActif', CheckboxType::class, [
                'label' => 'Produit actif (visible sur le site)',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
            'is_edit'    => false,
        ]);
        $resolver->setAllowedTypes('is_edit', 'bool');
    }
}
