<?php

namespace Pruebas\DBHmi2GuaycuruCamasBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ORM\Mapping as ORM;

/**
 * Habitaciones
 *
 * @ORM\Table(name="habitaciones", uniqueConstraints={@ORM\UniqueConstraint(name="idx_unique_nombre_id_sala", columns={"nombre", "id_sala"})}, indexes={@ORM\Index(name="idx_fk_habitaciones_id_sala", columns={"id_sala"})})
 * @ORM\Entity(repositoryClass="Pruebas\DBHmi2GuaycuruCamasBundle\Entity\HabitacionesRepository")
 * @UniqueEntity(
 *     fields={"nombre", "idSala"},
 *     message="El nombre de habitación: {{ value }}, ya existe en la sala."
 * )
 */
class Habitaciones
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_habitacion", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idHabitacion;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=50, nullable=false)
     * 
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "El nombre de habitación no puede superar los 50 caracteres"
     * )
     * 
     */
    private $nombre;

    /**
     * @var integer
     *
     * @Assert\Choice({1, 2, 3},
     *      message = "El valor de sexo: {{ value }} no es válido. Valores posibles 1=masculino, 2=femenino, 3=mixto"
     * )
     * 
     * @ORM\Column(name="sexo", type="integer", nullable=false)
     */
    private $sexo;

    /**
     * @var integer
     *
     * @Assert\Range(
     *      min = 0,
     *      max = 255,
     *      minMessage = "Edad desde: {{ value }} no válida. La edad mínima no puede ser un valor negativo",
     *      maxMessage = "Edad desde: {{ value }} no válida. La edad máxima no puede ser mayor a 255"
     * )
     * 
     * @ORM\Column(name="edad_desde", type="integer", nullable=false)
     */
    private $edadDesde = 0;

    /**
     * @var integer
     *
     * @Assert\Range(
     *      min = 0,
     *      max = 255,
     *      minMessage = "Edad hasta: {{ value }} no válida. La edad mínima no puede ser un valor negativo",
     *      maxMessage = "Edad hasta: {{ value }} no válida. La edad máxima no puede ser mayor a 255"
     * )
     * 
     * @ORM\Column(name="edad_hasta", type="integer", nullable=false)
     */
    private $edadHasta = 255;

    /**
     * @var string
     *
     * @Assert\Choice({"1", "2", "3", "4", "5"},
     *      message = "El valor de tipo edad: {{ value }} no es válido. Valores posibles 1=años, 2=meses, 3=días, 4=horas, 5=minutos"
     * )
     * 
     * @ORM\Column(name="tipo_edad", type="string", length=1, nullable=false)
     */
    private $tipoEdad;

    /**
     * @var integer
     *
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "Cantidad de camas: {{ value }} no válido. La habitación no puede tener un valor negativo de camas"
     * )
     * 
     * @ORM\Column(name="cant_camas", type="smallint", nullable=false)
     */
    private $cantCamas;

    /**
     * @var boolean
     *
     * @Assert\Type(
     *     type="bool",
     *     message="EL valor de baja: {{ value }} debe ser true o false."
     * )
     * 
     * @ORM\Column(name="baja", type="boolean", nullable=false)
     */
    private $baja = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_modificacion", type="datetime", nullable=false)
     */
    private $fechaModificacion = 'CURRENT_TIMESTAMP';

    /**
     * @var \Salas
     *
     * @ORM\ManyToOne(targetEntity="Salas")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_sala", referencedColumnName="id_sala")
     * })
     */
    private $idSala;



    /**
     * Get idHabitacion
     *
     * @return integer
     */
    public function getIdHabitacion()
    {
        return $this->idHabitacion;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Habitaciones
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set sexo
     *
     * @param integer $sexo
     *
     * @return Habitaciones
     */
    public function setSexo($sexo)
    {
        $this->sexo = $sexo;

        return $this;
    }

    /**
     * Get sexo
     *
     * @return integer
     */
    public function getSexo()
    {
        return $this->sexo;
    }

    /**
     * Set edadDesde
     *
     * @param integer $edadDesde
     *
     * @return Habitaciones
     */
    public function setEdadDesde($edadDesde)
    {
        $this->edadDesde = $edadDesde;

        return $this;
    }

    /**
     * Get edadDesde
     *
     * @return integer
     */
    public function getEdadDesde()
    {
        return $this->edadDesde;
    }

    /**
     * Set edadHasta
     *
     * @param integer $edadHasta
     *
     * @return Habitaciones
     */
    public function setEdadHasta($edadHasta)
    {
        $this->edadHasta = $edadHasta;

        return $this;
    }

    /**
     * Get edadHasta
     *
     * @return integer
     */
    public function getEdadHasta()
    {
        return $this->edadHasta;
    }

    /**
     * Set tipoEdad
     *
     * @param string $tipoEdad
     *
     * @return Habitaciones
     */
    public function setTipoEdad($tipoEdad)
    {
        $this->tipoEdad = $tipoEdad;

        return $this;
    }

    /**
     * Get tipoEdad
     *
     * @return string
     */
    public function getTipoEdad()
    {
        return $this->tipoEdad;
    }

    /**
     * Set cantCamas
     *
     * @param integer $cantCamas
     *
     * @return Habitaciones
     */
    public function setCantCamas($cantCamas)
    {
        $this->cantCamas = $cantCamas;

        return $this;
    }

    /**
     * Get cantCamas
     *
     * @return integer
     */
    public function getCantCamas()
    {
        return $this->cantCamas;
    }

    /**
     * Set baja
     *
     * @param boolean $baja
     *
     * @return Habitaciones
     */
    public function setBaja($baja)
    {
        $this->baja = $baja;

        return $this;
    }

    /**
     * Get baja
     *
     * @return boolean
     */
    public function getBaja()
    {
        return $this->baja;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     *
     * @return Habitaciones
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fechaModificacion = $fechaModificacion;

        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * Set idSala
     *
     * @param \Pruebas\DBHmi2GuaycuruCamasBundle\Entity\Salas $idSala
     *
     * @return Habitaciones
     */
    public function setIdSala(\Pruebas\DBHmi2GuaycuruCamasBundle\Entity\Salas $idSala = null)
    {
        $this->idSala = $idSala;

        return $this;
    }

    /**
     * Get idSala
     *
     * @return \Pruebas\DBHmi2GuaycuruCamasBundle\Entity\Salas
     */
    public function getIdSala()
    {
        return $this->idSala;
    }
}
