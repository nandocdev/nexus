<?php
namespace App\Http\Requests;

use Nexus\Modules\Http\FormRequest;

/**
 * Form request for user creation
 */
class StoreUserRequest extends FormRequest {
    protected $rules = [
        'name' => 'required|min:2|max:255',
        'email' => 'required|email',
        'password' => 'required|min:6'
    ];

    protected $messages = [
        'name.required' => 'El nombre es obligatorio',
        'name.min' => 'El nombre debe tener al menos :min caracteres',
        'email.required' => 'El email es obligatorio',
        'email.email' => 'El email debe tener un formato válido',
        'password.required' => 'La contraseña es obligatoria',
        'password.min' => 'La contraseña debe tener al menos :min caracteres'
    ];

    protected $attributes = [
        'name' => 'nombre',
        'email' => 'correo electrónico',
        'password' => 'contraseña'
    ];
}