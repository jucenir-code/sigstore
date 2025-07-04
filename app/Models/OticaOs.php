<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OticaOs extends Model
{
    use HasFactory;

    protected $fillable = [
        'ordem_servico_id', 'medico_id', 'validade', 'imagem_receita', 'observacao_receita', 'convenio_id', 'tipo_lente',
        'material_lente', 'descricao_lente', 'coloracao_lente', 'armacao_propria', 'armacao_segue', 'formato_armacao_id',
        'armacao_aro', 'armacao_ponte', 'armacao_maior_diagonal', 'armacao_altura_vertical', 'armacao_distancia_pupilar',
        'armacao_altura_centro_longe_od', 'armacao_altura_centro_longe_oe', 'armacao_altura_centro_perto_od', 
        'armacao_altura_centro_perto_oe', 'tratamentos'
    ];
}
