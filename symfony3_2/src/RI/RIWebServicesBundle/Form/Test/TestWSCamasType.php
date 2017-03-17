<?php

namespace RI\RIWebServicesBundle\Form\Test;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints\NotBlank;

use RI\RIWebServicesBundle\Utils\RI\RI;
use RI\RIWebServicesBundle\Utils\RI\RIUtiles;


/** 
 * WebServices testing
 * 
 *  Agregar cama
 *  ------------
 * 
 *   ["nombre_cama"]
 *   ["nombre_habitacion"]
 *   ["id_efector"]
 *   ["id_clasificacion_cama"]
 *   ["estado"]
 *   ["rotativa"]
 *   ["baja"]
 * 
 * 
 *  Baja
 *  ----
 * 
 *  id_clasificacion_cama
 *  id_habitacion
 *  baja
 *  estado
 * 
 *
 */
class TestWSCamasType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        

        // choices habitaciones
        $hab_choices = RIUtiles::getSalasHabitacionesChoices(72);
        
        // choices camas
        $camas_choices = RIUtiles::getSalasHabCamasChoices(72);
        
//        dump($camas_choices);die();
        
        $builder
            ->add(
                    'clasificaciones_camas', 
                    'Symfony\Bridge\Doctrine\Form\Type\EntityType',
                    array(
                        'label' => 'Clasificación',
                        'class' => RIUtiles::DB_BUNDLE.':ClasificacionesCamas',
                        'query_builder' => function (EntityRepository $er) {
                            
                            $qb = $er
                                    ->createQueryBuilder('cc')
                                    ->orderBy('cc.tipoCuidadoProgresivo', 'ASC');
                            
                            return $qb;
                        },
                        'choice_label' => 'clasificacionCama',
                        'group_by' => function($clasificacion_cama, $key, $index) {
                            
                            switch ($clasificacion_cama->getTipoCuidadoProgresivo()){
                                
                                case 0:
                                    
                                    return('Cuidado Moderado');
                                    
                                case 1:
                                    
                                    return('Cuidado Intermedio');
                                    
                                case 2:
                                    
                                    return('Cuidado Intensivo');
                                    
                            }
                        },
                    )
            )
            ->add(
                    'efectores', 
                    'Symfony\Bridge\Doctrine\Form\Type\EntityType',
                    array(
                        'label' => 'Efector',
                        'class' => RIUtiles::DB_BUNDLE.':Efectores',
                        'query_builder' => function (EntityRepository $er) {
                            
                            $qb = $er
                                    ->createQueryBuilder('e')
                                    ->where('e.idEfector = 72');    
                            
                            return $qb;
                        },
                        'choice_label' => 'nomEfector'
                    )
            )
            ->add(
                    'habitaciones',
                    'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                    array(
                        'choices' => $camas_choices,
                        'placeholder' => 'Seleccione una habitación',
                        'label' => 'Sala/Habitación',
                    )
            )
            ->add(
                    'nombre', 
                    'Symfony\Component\Form\Extension\Core\Type\TextType',
                    array(
                        'attr' => array(
                            'placeholder' => 'Nombre de la cama'
                            )
                        )
                    )
            // Estado: L=libre; O=ocupada; F=fuera de servicio; R=en reparacion; V=reservada
            ->add(
                    'estado', 
                    'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                    array(
                        
                        'choices' => array(
                            'Seleccione el estado de la cama' => '',
                            'Libre' => 'L',
                            'Ocupada' => 'O',
                            'Fuera de servicio' => 'F',
                            'En Reparación' => 'R',
                            'Reservada' => 'V'
                        ),
                        'constraints' => array(
                            new NotBlank(),
                        ),                        
                        'choice_attr' => function($val, $key, $index) {
                            
                            if ($val==''){
                                return array(
                                    'class' => 'ri-placeholder'
                                    );
                            }
                            return array();
                        },
                    )
                )
            ->add(
                    'rotativa', 
                    'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                    array(
                        'choices' => array(
                            'Si' => 1,
                            'No' => 0
                        ),
                        'attr' => array(
                            'placeholder' => 'Rotativa'
                        )
                    )
                )
            ->add(
                    'baja', 
                    'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                    array(
                        'choices' => array(
                            'Si' => 1,
                            'No' => 0
                        ),
                        'attr' => array(
                            'placeholder' => 'Baja'
                        )
                    )
                )
            ->add(
                    'bt_agregar', 
                    'Symfony\Component\Form\Extension\Core\Type\SubmitType',
                    array(
                        'label' => 'Agregar'
                    )
            )
            ->add(
                    'bt_modificar', 
                    'Symfony\Component\Form\Extension\Core\Type\SubmitType',
                    array(
                        'label' => 'Modificar'
                    )
            )
            ->add(
                    'bt_eliminar', 
                    'Symfony\Component\Form\Extension\Core\Type\SubmitType',
                    array(
                        'label' => 'Eliminar'
                    )
            )
            ->add(
                    'bt_liberar', 
                    'Symfony\Component\Form\Extension\Core\Type\SubmitType',
                    array(
                        'label' => 'Liberar'
                    )
            )
            ->add(
                    'bt_ocupar', 
                    'Symfony\Component\Form\Extension\Core\Type\SubmitType',
                    array(
                        'label' => 'Ocupar'
                    )
            )
            ->add(
                    'bt_baja', 
                    'Symfony\Component\Form\Extension\Core\Type\SubmitType',
                    array(
                        'label' => 'Baja'
                    )
            );
                        
        
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        
    }
}