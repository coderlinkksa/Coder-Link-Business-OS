<?php

namespace App\Shared\Models;

use App\Shared\Traits\HasAuditStamps;
use App\Shared\Traits\HasOwnerReference;
use App\Shared\Traits\SoftDeletable;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use HasAuditStamps;
    use HasOwnerReference;
    use SoftDeletable;
}
