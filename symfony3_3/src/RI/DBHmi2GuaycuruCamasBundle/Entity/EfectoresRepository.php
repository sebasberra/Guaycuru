<?php
/**
 * Proyecto Final Ingeniería Informática 2017 - UNL - Santa Fe - Argentina
 * 
 * Web Services Plataforma Web para centralización de camas críticas de internación en hospitales de la Provincia de Santa Fe
 * 
 * @author Sebastián Berra <sebasberra@yahoo.com.ar>
 * 
 * @version 0.1.0
 */
namespace RI\DBHmi2GuaycuruCamasBundle\Entity;

use Doctrine\ORM\EntityRepository;

use RI\RIWebServicesBundle\Utils\RI\RIUtiles;

/**
 * **Repositorio del objeto Doctrine Efectores**
 * 
 * @api *Librería de acceso a la base de datos centralizada del sistema de camas críticas de internación*
 * 
 * @author Sebastián Berra <sebasberra@yahoo.com.ar>
 *  
 * @link http://www.doctrine-project.org
 * Doctrine Project
 * 
 * @link https://symfony.com/doc/current/doctrine.html
 * Symfony - Databases and the Doctrine ORM
 *
 */
class EfectoresRepository extends EntityRepository
{
    
    /**
     * Obtiene los efectores activos en el sistema
     * 
     * @return Efectores
     * @throws \Exception Las excepciones son capturadas y relanzadas
     */
    public function findByConfiguracionesSistemas()
    {
                        
        $dql =
            "SELECT "
                ."e "
            ."FROM "
                .RIUtiles::DB_BUNDLE.":Efectores e "
            ."INNER JOIN "
                .RIUtiles::DB_BUNDLE.":ConfiguracionesSistemas cs "
            ."WHERE "
                ."cs.activa   = 1 "
            ."AND e.idEfector = cs.idEfector ";
        
        try{
            
            $query = $this->getEntityManager()->createQuery($dql);
            
            $efectores = $query->getResult();
            
        } catch (\Exception $e) {

            throw $e;
        }
        
        return $efectores;
    }
    
}

