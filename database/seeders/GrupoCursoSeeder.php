<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\GrupoCurso;
use Illuminate\Database\Seeder;

class GrupoCursoSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener todos los cursos creados
        $cursos = Curso::all();

        foreach ($cursos as $curso) {
            // Crear 2-3 grupos por curso con diferentes horarios
            $grupos = $this->generarGrupos($curso);
            
            foreach ($grupos as $grupo) {
                GrupoCurso::create($grupo);
            }
        }
    }

    private function generarGrupos($curso)
    {
        $grupos = [];