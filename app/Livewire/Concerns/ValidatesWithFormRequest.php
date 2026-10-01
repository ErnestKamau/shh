<?php

namespace App\Livewire\Concerns;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Validate Livewire state against a Form Request's rules and messages, so the same
 * validation backs both HTTP endpoints and Livewire actions.
 */
trait ValidatesWithFormRequest
{
    /**
     * @param  class-string<FormRequest>  $requestClass
     * @param  array<string, mixed>  $data
     * @param  string  $errorPrefix  Prepended to error keys so they match component properties (e.g. "form.").
     * @param  list<string>  $unprefixedKeys  Top-level keys that already match a component property (e.g. "lines", "file").
     * @param  list<string>|null  $onlyKeys  Validate only rules whose key is, or starts with, one of these (wizard steps).
     * @return array<string, mixed>
     */
    protected function validateWithFormRequest(
        string $requestClass,
        array $data,
        string $errorPrefix = '',
        array $unprefixedKeys = [],
        ?array $onlyKeys = null,
    ): array {
        $request = new $requestClass;
        $rules = $request->rules();

        if ($onlyKeys !== null) {
            $rules = array_filter(
                $rules,
                static fn (string $key): bool => collect($onlyKeys)->contains(
                    static fn (string $only): bool => $key === $only || Str::startsWith($key, $only.'.')
                ),
                ARRAY_FILTER_USE_KEY,
            );
        }

        $validator = Validator::make($data, $rules, $request->messages(), $request->attributes());

        if ($validator->fails()) {
            $this->throwPrefixed($validator->errors()->messages(), $errorPrefix, $unprefixedKeys);
        }

        return $validator->validated();
    }

    /**
     * Re-key a service ValidationException onto component properties.
     *
     * @param  list<string>  $unprefixedKeys
     */
    protected function rethrowWithPrefix(ValidationException $exception, string $errorPrefix, array $unprefixedKeys = []): never
    {
        $this->throwPrefixed($exception->errors(), $errorPrefix, $unprefixedKeys);
    }

    /**
     * @param  array<string, array<int, string>>  $errors
     * @param  list<string>  $unprefixedKeys
     */
    private function throwPrefixed(array $errors, string $errorPrefix, array $unprefixedKeys): never
    {
        $mapped = [];
        foreach ($errors as $key => $messages) {
            $root = Str::before((string) $key, '.');
            $mapped[in_array($root, $unprefixedKeys, true) ? $key : $errorPrefix.$key] = $messages;
        }

        throw ValidationException::withMessages($mapped);
    }
}
