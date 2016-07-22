<?php
namespace CodeHouse\Highway\Annotations;

/**
 * Annotation that can be used to signal to the router that specified class or method has a security constraint
 *
 * @since  1.0
 * @author Markus Karileet <markus.karileet@codehouse.ee>
 *
 * @Annotation
 * @Attributes({
 *    @Attribute("role",  required = true,  type = "string")
 * })
 */
class Security
{
    /**
     * The name of the role that is allowed to execute specified method
     *
     * @var string
     */
    public $role;
}