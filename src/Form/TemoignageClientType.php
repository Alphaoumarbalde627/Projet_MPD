<?php

namespace App\Form;

use App\Entity\Temoignage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class TemoignageClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomClient', TextType::class, [
                'label' => 'Votre nom',
                'constraints' => [
                    new NotBlank(['message' => 'Votre nom est obligatoire.']),
                    new Length(['max' => 255, 'maxMessage' => 'Le nom ne doit pas dépasser 255 caractères.']),
                ],
                'attr' => [
                    'placeholder' => 'Ex: Jean Dupont',
                    'required' => 'required',
                    'minlength' => '2',
                    'maxlength' => '255',
                ],
            ])
            ->add('fonctionClient', TextType::class, [
                'label' => 'Profil (optionnel)',
                'required' => false,
                'constraints' => [new Length(['max' => 255])],
                'attr' => [
                    'placeholder' => 'Ex: PDG, Développeur, Client',
                    'maxlength' => '255',
                ],
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Votre avis',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Partagez votre expérience avec ce produit...',
                ],
            ])
            ->add('note', IntegerType::class, [
                'label' => 'Note (1 a 5)',
                'required' => false,
                'empty_data' => '5',
                'constraints' => [
                    new Range([
                        'min' => 1,
                        'max' => 5,
                        'notInRangeMessage' => 'La note doit etre entre {{ min }} et {{ max }}.',
                    ]),
                ],
                'attr' => ['min' => 1, 'max' => 5, 'step' => '1'],
                'help' => '1 = pas satisfait, 5 = très satisfait',
            ])
            ->add('videoFile', FileType::class, [
                'label' => 'Votre video',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'La video est obligatoire.']),
                    new File([
                        'maxSize' => '50M',
                        'mimeTypes' => ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'],
                        'mimeTypesMessage' => 'Veuillez envoyer une video MP4, WEBM, OGG ou MOV.',
                        'maxSizeMessage' => 'Votre video depasse 50 Mo. Veuillez compresser ou reduire la duree.',
                        'uploadIniSizeErrorMessage' => 'Le fichier est trop volumineux pour le serveur (max 50 Mo).',
                    ]),
                ],
                'attr' => [
                    'accept' => 'video/mp4,video/webm,video/ogg,video/quicktime,.mov',
                    'required' => 'required',
                ],
                'help' => 'Max 50 Mo — Formats: MP4, WEBM, OGG, MOV',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Temoignage::class,
        ]);
    }
}
