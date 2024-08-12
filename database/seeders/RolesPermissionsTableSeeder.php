<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolesPermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //permissions concernant l'admin
        Permission::create(['name' => 'Ajouter une municipalite']);
        Permission::create(['name' => 'Supprimer une municipalite']);
        Permission::create(['name' => 'Voir la liste des municipalites']);
        Permission::create(['name' => 'Voir details une municipalite']);
        Permission::create(['name' => 'Modifier une municipalite']);
        Permission::create(['name' => 'Voir la liste des habitants']);
        Permission::create(['name' => 'Bloquer un compte']);

        //permissions concernant les municipalites
        Permission::create(['name' => 'Ajouter un projet']);
        Permission::create(['name' => 'Modifier un projet']);
        Permission::create(['name' => 'Supprimer un projet']);
        Permission::create(['name' => 'Voir la liste des projets']);
        Permission::create(['name' => 'Voir details un projet']);
        Permission::create(['name' => 'Approuver un projet']);
        Permission::create(['name' => 'desapprouver un projet']);
        Permission::create(['name' => 'Modifier le statut d un projet']);

        //permission concernant les habitants
        Permission::create(['name' => 'Soumettre un vote']);
        Permission::create(['name' => 'Ajouter un commentaire']);
        Permission::create(['name' => 'Modifier un commentaire']);
        Permission::create(['name' => 'Supprimer un commentaire']);
        Permission::create(['name' => 'Modifier son profil']);
        
    



    //attibutions des permissions aux roles

    $role = Role::create(['name' => 'admin']);
    $role->givePermissionTo(Permission::all());

    $role = Role::create(['name' => 'municipalite']);
    $role->givePermissionTo(
        'Ajouter un projet',
        'Modifier un projet',
        'Supprimer un projet',
        'Voir la liste des projets',
        'Voir details un projet',
        'Approuver un projet',
        'desapprouver un projet',
        'Modifier le statut d un projet',
        'Modifier une municipalite',
        'Voir details une municipalite'
    );

    $role = Role::create(['name' => 'habitant']);
    $role->givePermissionTo(
        'Voir la liste des projets',
        'Voir details un projet',
        'Ajouter un projet',
        'Modifier un projet',
        'Supprimer un projet',
        'Soumettre un vote',
        'Ajouter un commentaire',
        'Modifier un commentaire',
        'Supprimer un commentaire',
        'Modifier son profil'
    );
}
}
