<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'monto_prestamo',
        'tasa_interes',
        'cuotas',
        'modalidad_credito',
        'destino_credito',
        'periodo_credito',
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
}