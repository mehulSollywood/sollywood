<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\BranchTranslation
 *
 * @property int $id
 * @property int $branch_id
 * @property string|null $title
 * @property string $address
 * @property string $locale
 * @method static Builder|BranchTranslation newModelQuery()
 * @method static Builder|BranchTranslation newQuery()
 * @method static Builder|BranchTranslation query()
 * @method static Builder|BranchTranslation whereAddress($value)
 * @method static Builder|BranchTranslation whereBranchId($value)
 * @method static Builder|BranchTranslation whereId($value)
 * @method static Builder|BranchTranslation whereLocale($value)
 * @method static Builder|BranchTranslation whereTitle($value)
 * @mixin Eloquent
 */
class BranchTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['locale', 'title', 'address'];
    public $timestamps = false;

}
