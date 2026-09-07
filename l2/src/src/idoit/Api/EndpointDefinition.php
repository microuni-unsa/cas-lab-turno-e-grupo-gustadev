<?php

namespace idoit\Api;

use idoit\Api\Parameter\OptionalParameter;
use idoit\Api\Parameter\RequiredParameter;
use idoit\Module\Api\Exception\JsonRpc\ParameterException;
use isys_helper_textformat;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * Endpoint definition.
 *
 * Each endpoint using the 'EndpointInterface' will need to return a definition which contains
 * a name, a description and all mandatory and optional parameters.
 *
 * @see       API-484
 * @package   idoit\Api
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class EndpointDefinition
{
    private string $name;
    private string $description;
    private array $optionalParameters;
    private array $requiredParameters;
    private array|null $exampleRequest;
    private array|null $exampleResponse;
    private \isys_component_template_language_manager $language;

    public function __construct(string $name, string $description, array $definition = [], array|null $exampleRequest = null, array|null $exampleResponse = null)
    {
        $this->name = $name;
        $this->description = $description;
        $this->exampleRequest = $exampleRequest;
        $this->exampleResponse = $exampleResponse;
        $this->language = \isys_application::instance()->container->get('language');

        $optional = [];
        $required = [];

        foreach ($definition as $item) {
            if ($item instanceof OptionalParameter) {
                $optional[] = $item;
            } else {
                $required[] = $item;
            }
        }

        $this->setOptionalParameters($optional);
        $this->setRequiredParameters($required);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getExampleRequest(): ?array
    {
        return $this->exampleRequest;
    }

    public function getExampleResponse(): ?array
    {
        return $this->exampleResponse;
    }

    public function getRequiredParameters(): array
    {
        return $this->requiredParameters;
    }

    public function getOptionalParameters(): array
    {
        return $this->optionalParameters;
    }

    public function setOptionalParameters(array $parameters): void
    {
        $this->optionalParameters = [];

        foreach ($parameters as $parameter) {
            $this->addOptionalParameters($parameter);
        }
    }

    public function addOptionalParameters(OptionalParameter $parameter): void
    {
        $this->optionalParameters[$parameter->getName()] = $parameter;
    }

    public function setRequiredParameters(array $parameters): void
    {
        $this->requiredParameters = [];

        foreach ($parameters as $parameter) {
            $this->addRequiredParameters($parameter);
        }
    }

    public function addRequiredParameters(RequiredParameter $parameter): void
    {
        $this->requiredParameters[$parameter->getName()] = $parameter;
    }

    public function validateRequest(Request $request): void
    {
        $postData = $request->request->all();

        // Remove some passed parameters before validation.
        unset($postData['apikey'], $postData['language']);

        foreach ($this->requiredParameters as $name => $parameter) {
            /** @var RequiredParameter $parameter */
            if (!isset($postData[$name])) {
                throw new ParameterException("The required parameter '{$name}' was not passed.");
            }

            $dataType = gettype($postData[$name]);

            if (!in_array($dataType, $parameter->getTypes(), true)) {
                $types = isys_helper_textformat::this_this_or_that($parameter->getTypes());
                throw new ParameterException("The required parameter '{$name}' expects type '{$types}' but got '{$dataType}'.");
            }

            if ($parameter->getValidation() === null) {
                continue;
            }

            try {
                if (!$parameter->getValidation()($postData[$name])) {
                    // @see ID-11229 Translate descriptions.
                    $translatedDescription = $this->language->get($parameter->getDescription());
                    throw new ParameterException("The required parameter '{$name}' could not be validated, the description says: '{$translatedDescription}'.");
                }
            } catch (ParameterException $e) {
                throw $e;
            } catch (Throwable $e) {
                throw new ParameterException("The required parameter '{$name}' could not be validated, the error says: '{$e->getMessage()}'.");
            }
        }

        foreach ($this->optionalParameters as $name => $parameter) {
            /** @var OptionalParameter $parameter */
            if (!isset($postData[$name])) {
                $request->request->set($name, $parameter->getDefaultValue());

                continue;
            }

            $dataType = gettype($postData[$name]);

            if (!in_array($dataType, $parameter->getTypes(), true)) {
                $types = isys_helper_textformat::this_this_or_that($parameter->getTypes());
                throw new ParameterException("The optional parameter '{$name}' expects type '{$types}' but got '{$dataType}'.");
            }

            if ($parameter->getValidation() === null) {
                continue;
            }

            try {
                if (!$parameter->getValidation()($postData[$name])) {
                    // @see ID-11229 Translate descriptions.
                    $translatedDescription = $this->language->get($parameter->getDescription());
                    throw new ParameterException("The optional parameter '{$name}' could not be validated, the description says: '{$translatedDescription}'.");
                }
            } catch (ParameterException $e) {
                throw $e;
            } catch (Throwable $e) {
                throw new ParameterException("The optional parameter '{$name}' could not be validated, the error says: '{$e->getMessage()}'.");
            }
        }

        $passedButUnused = array_diff(array_keys($postData), array_merge(array_keys($this->optionalParameters), array_keys($this->requiredParameters)));

        if (count($passedButUnused)) {
            throw new ParameterException("The passed parameters '" . implode("', '", $passedButUnused) . "' are not part of this endpoint definition.");
        }
    }
}
