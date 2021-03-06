<?php


namespace ImpressCMS\Core\Extensions\Editors;

use ImpressCMS\Core\View\Form\Elements\TextAreaElement;

/**
 * If class implements this interface it means, that it is some kind editor
 *
 * @package icms\plugins
 */
interface EditorInterface
{

	/**
	 * gets text that will be displayed for user
	 *
	 * @return string
	 */
	public function getTitle(): string;

	/**
	 * Get editor version
	 *
	 * @return string
	 */
	public function getVersion(): string;

	/**
	 * Get license for editor
	 *
	 * @return string
	 */
	public function getLicense(): string;

	/**
	 * Create editor instance from config
	 *
	 * @param array $configs Editor configuration
	 * @param bool $checkCompatible If true, throws exception on failure
	 *
	 * @return TextAreaElement
	 */
	public function create(array $configs, $checkCompatible = false): TextAreaElement;

	/**
	 * Get order for sorting when displaying editor list
	 * Null means that editor doesn't care for the order
	 *
	 * @return int|null
	 */
	public function getOrder(): ?int;

	/**
	 * Gets if editor supports HTML markdown (aka inverted No HTML)
	 *
	 * @return bool
	 */
	public function supportsHTML(): bool;

}