<?php

namespace App\Domain;

final class Skill
{
    public function __construct(
        public int $id,
        public string $name,
        public int $level,
        public int $yearsExperience,
        public array $tags = [],
        public int $statusId = 1,
    ) {}
}
