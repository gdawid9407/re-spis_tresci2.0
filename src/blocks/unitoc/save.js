import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
  const { depth, min_headings, numbering, collapse } = attributes;
  const blockProps = useBlockProps.save();

  // Generuje shortcode z atrybutami bloku
  const shortcode = `[unitoc depth=\"${depth}\" min_headings=\"${min_headings}\" numbering=\"${numbering ? 1 : 0}\" collapse=\"${collapse ? 1 : 0}\"]`;

  return (
    <div {...blockProps}>
      {/* Wstawia wygenerowany shortcode */}
      <span dangerouslySetInnerHTML={{ __html: shortcode }} />
    </div>
  );
}
