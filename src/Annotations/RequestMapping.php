<?php
namespace CodeHouse\Highway\Annotations;

/**
 * Annotation that can be used to signal to the router that specified class or method has a request mapping
 *
 * @since  1.0
 * @author Markus Karileet <markus.karileet@codehouse.ee>
 *
 * @Annotation
 * @Attributes({
 *    @Attribute("value",  required = true,  type = "string"),
 *    @Attribute("method", required = false, type = "string")
 * })
 */
class RequestMapping
{
    /**
     * The value of URL to map, eg users/edit/{id}
     *
     * @var string
     */
    public $value;

    /**
     * HTTP method to intercept: GET POST
     *
     * @var string
     */
    public $method;
}