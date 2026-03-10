<?php
// This file is generated. Do not modify it manually.
return array(
	'block' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'wp-agent-ai/content-generator',
		'version' => '1.0.0',
		'title' => 'AI Content Generator',
		'category' => 'widgets',
		'description' => 'Generate AI-powered content directly in the editor.',
		'textdomain' => 'wp-agent-ai',
		'attributes' => array(
			'prompt' => array(
				'type' => 'string',
				'default' => ''
			),
			'contentType' => array(
				'type' => 'string',
				'default' => 'paragraph'
			),
			'tone' => array(
				'type' => 'string',
				'default' => 'professional'
			),
			'length' => array(
				'type' => 'string',
				'default' => 'medium'
			),
			'generatedContent' => array(
				'type' => 'string',
				'default' => ''
			),
			'isLoading' => array(
				'type' => 'boolean',
				'default' => false
			),
			'errorMessage' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./editor.scss'
	)
);
