<?php

namespace Pruebas\WSBundle\Utils;

use Pruebas\DBHmi2GuaycuruCamasBundle\Entity\Camas;
use Pruebas\DBHmi2GuaycuruCamasBundle\Entity\Habitaciones;

use Pruebas\WSBundle\Utils\ConfiguracionEdiliciaUtiles;

/** Realiza el acceso a la DB hmi2guaycuru para actualizacion de
 *  la configuracion edilicia.
 *  NOTA: la sincronizacion entre efectores y base central no puede
 *  ser completa en toda la estructura. Se inicia con la restriccion
 *  a implementar en las bases de hospitales que es nombre unico de
 *  cama por efector. La jararquia hacia arriba de camas, o sea, las
 *  relaciones con tabla habitaciones y salas no se implementara. Se
 *  sugiere nombres de habitaciones unicas por efector para que la 
 *  informacion se refleje en la base central, pero de no ser posible
 *  no se indicara la habitacion de la cama en base central
 */
class ConfiguracionEdilicia
{
    
    public static $ERROR_DEBUG;
    
    private $doctrine;
    private $em;
    private $conn;
    private $validator;
    
    private $utiles;
    
    public function __construct($doctrine,$validator) {
    
        
        self::$ERROR_DEBUG="";
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getManager('default');
        $this->conn = $doctrine->getManager('default')->getConnection();
        $this->validator = $validator;
        
        $this->utiles = new ConfiguracionEdiliciaUtiles(
                $this->doctrine,
                self::$ERROR_DEBUG);
        
    }
    
    public function __destruct() {
    
        
    }

    /** Agrega una cama a la base centralizada
     *  El parametro $nueva_cama es un arreglo con:
     *  ["nombre_cama"]
     *  ["nombre_habitacion"]
     *  ["id_efector"]
     *  ["id_clasificacion_cama"]
     *  ["estado"]
     *  ["rotativa"]
     *  ["baja"]
     * 
     * @param type $nueva_cama
     * @param type $msg
     * @return boolean
     */
    public function agregarCama(
            $nueva_cama){
        
//        id_cama int(10) unsigned NOT NULL AUTO_INCREMENT,
//        id_clasificacion_cama tinyint(3) unsigned NOT NULL 
//          COMMENT 'clasificacion de cama',
//        id_efector int(10) unsigned NOT NULL 
//          COMMENT 'Se guarda el id del efector para cuando la cama no esta asignada a una habitacion',
//        id_habitacion int(10) unsigned DEFAULT NULL 
//          COMMENT 'para camas rotativas esta permitido que la cama no este asignada a una habitacion en un momento dado',
//        NOTA: se agrega nombre de la habitacion y se busca el id_habitacion en base central por nombre
//        nombre_habitacion VARCHAR(50)
//        id_internacion int(10) unsigned DEFAULT NULL 
//          COMMENT 'Id de internacion de quien esta ocupando la cama. Si es NULL la cama esta vacia',
//        nombre varchar(50) NOT NULL,
//        estado char(1) NOT NULL 
//          COMMENT 'L=libre; O=ocupada; F=fuera de servicio; R=en reparacion; V=reservada',
//        rotativa tinyint(1) NOT NULL DEFAULT '0' 
//          COMMENT '0=no es rotativa, 1=es rotativa; Las camas rotativas pueden cambiarse de habitacion o sala o no estar asignada a una habitacion en un momento dado',
//        baja tinyint(1) NOT NULL DEFAULT '0' 
//          COMMENT '0 = habilitada; 1 = baja',
//        fecha_modificacion TIMESTAMP de actualizacion del registro
        
        
        
        // clasificacion cama
        try {
        
            $clasificacion_cama =
                    $this->utiles->getClasificacionCama(
                            $nueva_cama["id_clasificacion_cama"]
                            );
        
        } catch (\Exception $e) {

            throw $e;
            
        }
        
        
        // efector
        try{
            
            $efector = $this->utiles->getEfector($nueva_cama["id_efector"]);
            
        } catch (\Exception $e) {

            throw $e;
        }
        
        // rotativa
        $rotativa = $this->utiles->wrapBoolean($nueva_cama["rotativa"]);
        
        // cama rotativa entonces puede tener la habitacion null, en otro caso
        // controla que exista la habitacion
        if ($rotativa==true &&                
                $nueva_cama["nombre_sala"]="" &&
                $$nueva_cama["nombre_habitacion"] = ""){
            
            // cama rotativa sin habitacion asignada
            $habitacion = null;
            
        }else{
            
            // habitacion
            try{

                $habitacion = $this->utiles->getHabitacion(
                        $nueva_cama["id_efector"],
                        $nueva_cama["nombre_sala"],
                        $nueva_cama["nombre_habitacion"]);

            } catch (\ErrorException $ee){

                $msg = $ee->getMessage();

                throw $ee;

            }
            
        }
        
         
                     
        // objeto Camas doctrine
        $cama = new Camas();
        
        
        // baja
        $cama->setBaja($this->utiles->wrapBoolean($nueva_cama["baja"]));
        
        // estado libre
        $cama->setEstado($nueva_cama["estado"]);
        
        // timestamp fecha modificacion
        $cama->setFechaModificacion(null);
        
        // clasificacion cama
        $cama->setIdClasificacionCama($clasificacion_cama);
        
        
        // efector
        $cama->setIdEfector($efector);
        
        // habitacion
        $cama->setIdHabitacion($habitacion);
        
        // internacion null
        $cama->setIdInternacion(null);
        
        // nombre de cama
        $cama->setNombre($nueva_cama["nombre_cama"]);
        
        // rotativa
        $cama->setRotativa($rotativa);
        
        
        // validacion assert
        $this->validacionAssert($cama);
    
        

        // TODO transaction
        // 
        
        // begintrans
        $this->conn->beginTransaction();
        
        try{
        
            //
            // insert datos en la DB
            $this->em->persist($cama);
            $this->em->flush();

            // check actualiza cant camas de habitacion y sala
            if ($habitacion){

                // cant_camas habitacion
                $this->setCantCamasHab($habitacion);

                // cant_camas sala
                $this->setCantCamasSala($habitacion->getIdSala());

            }
        
            // commit
            $this->conn->commit();
            
        }catch(\Exception $e){
            
            self::$ERROR_DEBUG .=
                    "Error al agregar la cama: "
                    .$nueva_cama["nombre_cama"];
            
            // rollback
            $this->conn->rollBack();
            
            throw $e;
            
        }
        
        $msg = "La cama: ".$nueva_cama["nombre_cama"]
                ." fue ingresada al efector: "
                .$efector->getNomEfector();
        if ($habitacion){        
            $msg.=" en la sala: "
                    .$nueva_cama["nombre_sala"]
                    ." y la habitación: "
                    .$nueva_cama["nombre_habitacion"];
        }
        
        return $msg;
        
    }
    
    /** La modificacion de cama se aplica a
     * 
     *  id_clasificacion_cama
     *  id_habitacion
     *  baja
     *  estado
     * 
     * 
     * 
     * 
     *  El cambio de nombre es un caso especial y se trata 
     *  independiente porque es clave unica (nombre,id_efector)
     *  
     * @param type $modif_cama
     * @return boolean
     */
    public function modificarCama($modif_cama){
    
        
        // cama
        try {
            
            $cama = 
                    $this->utiles->getCama(
                            $modif_cama['nombre_cama'], 
                            $modif_cama['id_efector']
                            );
            
        } catch (\Exception $e) {
            
            throw $e;

        }
        
        
        // clasificacion cama
        try {
        
            $clasificacion_cama =
                    $this->utiles->getClasificacionCama(
                            $modif_cama["id_clasificacion_cama"]
                            );
        
        } catch (\Exception $e) {

            throw $e;
            
        }
        
        
        // efector
        try{
            
            $efector = 
                    $this->utiles->getEfector(
                            $modif_cama["id_efector"]
                            );
            
        } catch (\Exception $e) {

            throw $e;
        }
        
        
        
        // rotativa
        $rotativa = $this->utiles->wrapBoolean($modif_cama["rotativa"]);
        
        // cama rotativa entonces puede tener la habitacion null, en otro caso
        // controla que exista la habitacion
        if ($rotativa==true &&                
                $nueva_cama["nombre_sala"]="" &&
                $$nueva_cama["nombre_habitacion"] = ""){
            
            // cama rotativa sin habitacion asignada
            $habitacion = null;
            
        }else{
            
            // habitacion
            try{

                $habitacion = $this->utiles->getHabitacion(
                        $nueva_cama["id_efector"],
                        $nueva_cama["nombre_sala"],
                        $nueva_cama["nombre_habitacion"]);

            } catch (\ErrorException $ee){

                $msg = $ee->getMessage();

                throw $ee;

            }
            
        }
        
        
        
        // baja
        $cama->setBaja($this->utiles->wrapBoolean($modif_cama["baja"]));
        
        // estado libre
        $cama->setEstado($modif_cama["estado"]);
        
        // timestamp fecha modificacion
        $cama->setFechaModificacion(null);
               
        // clasificacion cama
        $cama->setIdClasificacionCama($clasificacion_cama);
        
        // habitacion
        $cama->setIdHabitacion($habitacion);
                
        // internacion null
        $cama->setIdInternacion(null);
        
        // rotativa
        $cama->setRotativa($rotativa);
        
        
        // validacion assert
        $this->validacionAssert($cama);
        
        // update datos en la DB
        $this->em->persist($cama);
        $this->em->flush();

        $msg = "La cama: ".$modif_cama["nombre_cama"]
                ." fue modificada en el efector: "
                .$efector->getNomEfector();
        if ($habitacion){        
            $msg.=" en la sala: "
                    .$modif_cama["nombre_sala"]
                    ." y la habitación: "
                    .$modif_cama["nombre_habitacion"];
        }
        
        return $msg;
        
    }
    
    
    /** Elimina la cama si no tiene usando DELETE, la baja se hace
     *  a traves de la modificacion de cama
     *  
     * @param type $elimina_cama
     * @throws \Exception
     */
    public function eliminarCama($elimina_cama){
        
        // cama
        try {
            
            $cama = 
                    $this->utiles->getCama(
                            $elimina_cama['nombre_cama'], 
                            $elimina_cama['id_efector']
                            );
            
        } catch (\Exception $e) {
            
            throw $e;

        }
        
        // habitacion
        $habitacion=$cama->getIdHabitacion();
        
        
        // begintrans
        $this->conn->beginTransaction();
        
        try{
            
        
            // elimina la cama
            $this->em->remove($cama);
            $this->em->flush();

            // check actualiza cant camas de habitacion y sala
            if ($habitacion){

                // cant_camas habitacion
                $this->setCantCamasHab($habitacion);

                // cant_camas sala
                $this->setCantCamasSala($habitacion->getIdSala());

            }
            
            // commit
            $this->conn->commit();
            
        }catch(\Exception $e){
            
            self::$ERROR_DEBUG .=
                    "Error al eliminar la cama: "
                    .$elimina_cama["nombre_cama"];
            
            // rollback
            $this->conn->rollBack();
            
            throw $e;
            
        }
        
        $msg = "La cama: ".$elimina_cama["nombre_cama"]
                ." fue eliminada del efector: "
                .$cama->getIdEfector()->getNomEfector();
                
        return $msg;
        
    }
    
    public function ocuparCama(
            $ocupa_cama,
            $sobrecarga=false){
        
        // cama
        try {
            
            $cama = 
                    $this->utiles->getCama(
                            $ocupa_cama['nombre_cama'], 
                            $ocupa_cama['id_efector']
                            );
            
        } catch (\Exception $e) {
            
            throw $e;

        }
        
        // check cama ocupada
        if ($cama->getEstado()=='O' &&
            $sobrecarga==false){
                
            $msg = "La cama "
                .$ocupa_cama['nombre_cama']
                ." está ocupada";
            throw new \Exception($msg);
                
        }
        
        // estado
        $cama->setEstado('O');
        
        try{
        
            // ocupa la cama
            $this->em->persist($cama);
            $this->em->flush();
        
            // commit
            $this->conn->commit();
            
        } catch (\Exception $e) {

            // rollback
            $this->conn->rollBack();
            
            throw $e;
            
        }
        
        $msg = "La cama: ".$ocupa_cama["nombre_cama"]
                ." fue ocupada en el efector: "
                .$cama->getIdEfector()->getNomEfector();
                
        return $msg;
    }
    
    public function liberarCama($libera_cama){
        
        // cama
        try {
            
            $cama = 
                    $this->utiles->getCama(
                            $libera_cama['nombre_cama'], 
                            $libera_cama['id_efector']
                            );
            
        } catch (\Exception $e) {
            
            throw $e;

        }
        
        // check cama libre
        if ($cama->getEstado()=='L'){
                
            $msg = "La cama "
                .$libera_cama['nombre_cama']
                ." ya está libre";
            throw new \Exception($msg);
                
        }
        
        // estado
        $cama->setEstado('L');
        
        // begintrans
        $this->conn->beginTransaction();
        
        try{
        
            // ocupa la cama
            $this->em->persist($cama);
            $this->em->flush();
        
            // commit
            $this->conn->commit();
            
        } catch (\Exception $e) {

            // rollback
            $this->conn->rollBack();
            
            throw $e;
            
        }
        
        
        $msg = "La cama: ".$libera_cama["nombre_cama"]
                ." fue liberada en el efector: "
                .$cama->getIdEfector()->getNomEfector();
                
        return $msg;
        
    }
    
    public function agregarHabitacion($nueva_hab){
        
        
        // sala
        try {
        
            $sala =
                    $this->utiles->getSala(
                            $nueva_hab["nombre_sala"],
                            $nueva_hab["id_efector"]
                            );
            
        } catch (\Exception $e) {

            throw $e;
            
        }
        
                
        // objeto Habitaciones doctrine
        $habitacion = new Habitaciones();
        
        
        // baja
        $habitacion->setBaja($this->utiles->wrapBoolean($nueva_hab["baja"]));
        
        // sexo
        $habitacion->setSexo($nueva_hab["sexo"]);
        
        // edad_desde
        $habitacion->setEdadDesde($nueva_hab["edad_desde"]);
        
        // edad_hasta
        $habitacion->setEdadHasta($nueva_hab["edad_hasta"]);
        
        // tipo_edad
        $habitacion->setTipoEdad($nueva_hab["tipo_edad"]);

        // cant_camas
        $habitacion->setCantCamas(0);
        
        // timestamp fecha modificacion
        $habitacion->setFechaModificacion(null);
        
        // sala
        $habitacion->setIdSala($sala);
        
        // nombre de habitacion
        $habitacion->setNombre($nueva_hab["nombre_habitacion"]);
        
        
        
        // validacion assert
        $this->validacionAssert($habitacion);
    
        
        // begintrans
        $this->conn->beginTransaction();
        
        try{
        
            // insert datos en la DB
            $this->em->persist($habitacion);
            $this->em->flush();

            // commit
            $this->conn->commit();
            
        } catch (\Exception $e) {

            // rollback
            $this->conn->rollBack();
            
            throw $e;
            
        }
        
        
        $msg = "La habitación: ".$nueva_hab["nombre_habitacion"]
                ." fue ingresada en la sala: "
                .$sala->getNombre()
                ." del efector: "
                .$sala->getIdEfector()->getNomEfector();
        
        
        return $msg;
        
    }
    
    public function modificarHabitacion($modif_hab){
        
        // habitacion
        try {
        
            $habitacion =
                    $this->utiles->getHabitacion(
                            $modif_hab["nombre_habitacion"],
                            $modif_hab["nombre_sala"],
                            $modif_hab["id_efector"]
                            );
            
        } catch (\Exception $e) {

            throw $e;
            
        }
        
        
        
        // baja
        $habitacion->setBaja($this->utiles->wrapBoolean($modif_hab["baja"]));
        
        // sexo
        $habitacion->setSexo($modif_hab["sexo"]);
        
        // edad_desde
        $habitacion->setEdadDesde($modif_hab["edad_desde"]);
        
        // edad_hasta
        $habitacion->setEdadHasta($modif_hab["edad_hasta"]);
        
        // tipo_edad
        $habitacion->setTipoEdad($modif_hab["tipo_edad"]);
        
        // timestamp fecha modificacion
        $habitacion->setFechaModificacion(null);
        
        
        // validacion assert
        $this->validacionAssert($habitacion);
    
        
        // begintrans
        $this->conn->beginTransaction();
        
        try{
        
            // update datos en la DB
            $this->em->persist($habitacion);
            $this->em->flush();

            // check actualiza cant camas de habitacion y sala
            if ($habitacion){

                // cant_camas habitacion
                $this->setCantCamasHab($habitacion);

                // cant_camas sala
                $this->setCantCamasSala($habitacion->getIdSala());

            }
            
            // commit
            $this->conn->commit();
            
        } catch (\Exception $e) {

            self::$ERROR_DEBUG .=
                    "Error al modificar la habitación: "
                    .$modif_hab["nombre_habitacion"];
            
            // rollback
            $this->conn->rollBack();
            
            throw $e;
            
        }
        
        
        $msg = "La habitación: "
                .$modif_hab["nombre_habitacion"]
                ." de la sala: "
                .$modif_hab["nombre_sala"]
                ." fue modificada en el efector: "
                .$habitacion->getIdSala()->getIdEfector()->getNomEfector();
        
        
        return $msg;
        
    }
    
    public function eliminarHabitacion($elimina_hab){
        
        // habitacion
        try {
            
            $habitacion = 
                    $this->utiles->getHabitacion(
                            $elimina_hab["nombre_habitacion"],
                            $elimina_hab["nombre_sala"], 
                            $elimina_hab["id_efector"]
                            );
            
        } catch (\Exception $e) {
            
            throw $e;

        }
        
        
        
        // begintrans
        $this->conn->beginTransaction();
        
        try{
            
        
            // count camas habitacion
            $count = 
                $this->doctrine->getRepository
                    ('DBHmi2GuaycuruCamasBundle:Habitaciones')
                    ->countCamas(
                            $habitacion->getIdHabitacion());
            
            if ($count==0){
            
                // elimina la habitacion
                $this->em->remove($habitacion);
                                    
            }else{
                
                // set baja = true
                $habitacion->setBaja(true);
                
                // baja camas de la habitacion
                $this->setBajaCamasHabitacion($habitacion->getIdHabitacion());
                
                // cant_camas habitacion
                $this->setCantCamasHab($habitacion);

                // cant_camas sala
                $this->setCantCamasSala($habitacion->getIdSala());
                
            }

            // flush habitacion
            $this->em->flush();            
            
            // commit
            $this->conn->commit();
            
        }catch(\Exception $e){
            
            self::$ERROR_DEBUG .=
                    "Error al eliminar/baja la habitación: "
                    .$elimina_hab["nombre_habitacion"];
            
            // rollback
            $this->conn->rollBack();
            
            throw $e;
            
        }
        
        $msg = "La habitación: "
                .$elimina_hab["nombre_habitacion"]
                ." fue eliminada/baja de la sala: "
                .$habitacion->getIdSala()->getNombre()
                ." del efector: "
                .$habitacion->getIdSala()->getIdEfector()->getNombre();
                
        return $msg;
        
    }
    
    public function agregarSala($nueva_sala){
        
        
        // !!!TODO probar funciones de habitacion y modificaciones de
        // repositorio habitaciones countCamas !! ver en salas repositorio misma
        // modificacion
    }
    
    public function modificarSala($modif_sala){
        
    }
    
    public function eliminarSala($elimina_sala){
        
    }
    
    
    
            
    private function validacionAssert($entidad){
        
        //
        // validator assert
        //
        $errors = $this->validator->validate($entidad);
    
        if (count($errors) > 0) {
            /*
             * Uses a __toString method on the $errors variable which is a
             * ConstraintViolationList object. This gives us a nice string
             * for debugging.
             */
            self::$ERROR_DEBUG .= (string) $errors;

            // concatena los errores
            $msg="(1) ".$errors[0]->getMessage();
            for ($i=1;$i<count($errors);$i++){
                
                //$msg.=" (".($i+1).") ".$errors[$i]->getMessage();
                $msg.="<p> (".($i+1).") ".$errors[$i]->getMessage()."</p>";
                
            }
            
            throw new \Exception($msg);
        }
        
    }
    
        
    private function setCantCamasHab($habitacion){
        
        
        // count camas habitacion
        $count = 
            $this->doctrine->getRepository
                ('DBHmi2GuaycuruCamasBundle:Habitaciones')
                ->countCamas($habitacion->getIdHabitacion());
        
        // cant camas
        $habitacion->setCantCamas($count);
        
        // validacion assert
        $this->validacionAssert($habitacion);
        
        $this->em->persist($habitacion);
        $this->em->flush();
        
    }
    
    private function setCantCamasSala($sala){
        
        // count camas salas
        $count = 
            $this->doctrine->getRepository
                ('DBHmi2GuaycuruCamasBundle:Salas')
                ->countCamas($sala->getIdSala());
        
        // cant camas
        $sala->setCantCamas($count);
        
        
        // validacion assert
        $this->validacionAssert($sala);
        
        $this->em->persist($sala);
        $this->em->flush();
        
    }
    
    private function setBajaCamasHabitacion($id_habitacion){
        
        // baja cama de la habitacion
        $camas = 
            $this->doctrine->getRepository
                ('DBHmi2GuaycuruCamasBundle:Camas')
                ->findByIdHabitacion($id_habitacion);

        foreach($camas as $cama) {

            // cama baja = true
            $cama->setBaja(true);

            // estado = 'F' (fuera de servicio)
            $cama->setEstado('F');

            // validacion assert
            $this->validacionAssert($cama);
            
            $this->em->persist($cama);

        }
        
        $this->em->flush();
        
    }
            
}

