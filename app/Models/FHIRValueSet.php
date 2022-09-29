<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FHIRValueSet
 *
 * @property int $id
 * @property string $name
 * @property string $url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|FHIRValueSet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FHIRValueSet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FHIRValueSet query()
 * @method static \Illuminate\Database\Eloquent\Builder|FHIRValueSet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FHIRValueSet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FHIRValueSet whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FHIRValueSet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FHIRValueSet whereUrl($value)
 * @mixin \Eloquent
 */
class FHIRValueSet extends Model
{
    use HasFactory;

    protected $casts = [
        'url' => 'json'
    ];

}
