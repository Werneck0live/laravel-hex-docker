<?php
namespace App\Domain\Contracts;
use App\Domain\Skill;
interface SkillRepository { /** @return Skill[] */ public function all(): array; }
