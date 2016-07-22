<?php
namespace CodeHouse\Highway\Annotations;

/**
 * Annotation that can be used to signal to the router that specified class is a Controller
 *
 * @since  1.0
 * @author Markus Karileet <markus.karileet@codehouse.ee>
 *
 * @Annotation
 */
class Controller
{

    /**
     * @var boolean
     */
    public $required = false;
}