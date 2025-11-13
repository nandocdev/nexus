<?php
namespace Nexus\Modules\Http;

use Nexus\Modules\Validation\Validator;

/**
 * Base Form Request class for validation
 */
abstract class FormRequest {
    /**
     * The request instance
     *
     * @var Request
     */
    protected $request;

    /**
     * Validation rules
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Custom error messages
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Custom attribute names
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Whether to stop on first validation failure
     *
     * @var bool
     */
    protected $stopOnFirstFailure = false;

    /**
     * Create a new form request instance
     */
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
     * Get the validation rules
     */
    public function rules() {
        return $this->rules;
    }

    /**
     * Get custom error messages
     */
    public function messages() {
        return $this->messages;
    }

    /**
     * Get custom attribute names
     */
    public function attributes() {
        return $this->attributes;
    }

    /**
     * Get the data to validate
     */
    public function validationData() {
        return $this->request->all();
    }

    /**
     * Validate the request
     */
    public function validate() {
        $validator = new Validator($this->validationData(), $this->rules());

        if (!empty($this->messages())) {
            // Note: This would require extending Validator to support custom messages
            // For now, we'll use the basic validation
        }

        if (!$validator->validate()) {
            throw new \Nexus\Modules\Exception\ValidationException(
                'Validation failed',
                $validator->errors(),
                $this->validationData()
            );
        }

        return $validator;
    }

    /**
     * Get validated data
     */
    public function validated() {
        $this->validate();
        return $this->validationData();
    }

    /**
     * Check if validation passes
     */
    public function passes() {
        try {
            $this->validate();
            return true;
        } catch (\Nexus\Modules\Exception\ValidationException $e) {
            return false;
        }
    }

    /**
     * Check if validation fails
     */
    public function fails() {
        return !$this->passes();
    }

    /**
     * Get the errors
     */
    public function errors() {
        try {
            $this->validate();
            return [];
        } catch (\Nexus\Modules\Exception\ValidationException $e) {
            return $e->errors();
        }
    }

    /**
     * Authorize the request
     */
    public function authorize() {
        return true;
    }

    /**
     * Validate the request with authorization check
     */
    public function validateResolved() {
        if (!$this->authorize()) {
            throw new \Nexus\Modules\Exception\HttpException(403, 'Unauthorized');
        }

        return $this->validate();
    }
}