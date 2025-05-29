import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
  const { depth, minHeaders, numbering, collapse } = attributes;
  const blockProps = useBlockProps();

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Ustawienia spisu treści', 're-spis-tresci')} initialOpen>
          <RangeControl
            label={__('Maksymalna głębokość nagłówków', 're-spis-tresci')}
            value={depth}
            onChange={(value) => setAttributes({ depth: value })}
            min={1}
            max={6}
          />
          <RangeControl
            label={__('Minimalna liczba nagłówków', 're-spis-tresci')}
            value={minHeaders}
            onChange={(value) => setAttributes({ minHeaders: value })}
            min={1}
            max={10}
          />
          <ToggleControl
            label={__('Numerowanie pozycji', 're-spis-tresci')}
            checked={numbering}
            onChange={(value) => setAttributes({ numbering: value })}
          />
          <ToggleControl
            label={__(' możliwość zwijania', 're-spis-tresci')}
            checked={collapse}
            onChange={(value) => setAttributes({ collapse: value })}
          />
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}>
        {__('Podgląd spisu treści', 're-spis-tresci')}
      </div>
    </>
  );
}
