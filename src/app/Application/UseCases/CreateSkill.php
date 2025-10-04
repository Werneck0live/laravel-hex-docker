<?php

namespace App\Application\UseCases;

use App\Domain\Contracts\SkillWriter;
use App\Domain\Skill;

final class CreateSkill
{
    public function __construct(private readonly SkillWriter $writer) {}

    /** @param array{name:string,level:int,years_experience:int,tags?:array,status_id?:int} $payload */
    public function handle(array $payload): Skill
    {
        return $this->writer->create($payload);
    }
}
