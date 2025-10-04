<?php

namespace App\Domain\Contracts;

use App\Domain\Skill;

interface SkillWriter
{
    /** @param array{name:string,level:int,years_experience:int,tags?:array,status_id?:int} $payload */
    public function create(array $payload): Skill;
}
