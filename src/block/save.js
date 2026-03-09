import { useBlockProps } from '@wordpress/block-editor';

export default function save() {
	return (
		<div {...useBlockProps()}>
			<p>WP Agent AI Block</p>
		</div>
	);
}