<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EvaluacionCliente extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'evaluacion_cliente';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_Asesor',
        'id_Cliente',
        'producto',
        'montoPrestamo',
        'tasaInteres',
        'cuotas',
        'modalidadCredito',
        'destinoCredito',
        'periodoCredito',
        'estado',
        'observaciones',
    ];

    /**
     * Obtiene el usuario (cliente) asociado a esta evaluación.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_Cliente' , 'id');
    }

    /**
     * Obtiene el usuario (cliente) asociado a esta evaluación.
     */
    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_Asesor' , 'id');
    }
    
    /**
     * Relación con el AVAL (Corregida en el paso anterior).
     * Comparte 'id_Cliente' con la tabla 'cliente_avales'.
     */
    public function aval(): HasOne
    {
        return $this->hasOne(ClienteAval::class, 'id_Cliente', 'id_Cliente');
    }

    /**
     * Relación con UNIDAD FAMILIAR (Soluciona tu error actual).
     * La tabla 'unidad_familiar' tiene 'id_Evaluacion'.
     */
    public function unidadFamiliar(): HasOne
    {
        return $this->hasOne(UnidadFamiliar::class, 'id_Evaluacion', 'id');
    }

    /**
     * Relación con DATOS NEGOCIO.
     * La tabla 'datos_negocio' tiene 'id_Evaluacion'.
     */
    public function datosNegocio(): HasOne
    {
        return $this->hasOne(DatosNegocio::class, 'id_Evaluacion', 'id');
    }

    /**
     * Relación con GARANTÍAS.
     * La tabla 'garantias' tiene 'id_Evaluacion'.
     * Es HasMany porque una evaluación puede tener varias garantías.
     */
    public function garantias(): HasMany
    {
        return $this->hasMany(Garantia::class, 'id_Evaluacion', 'id');
    }


}