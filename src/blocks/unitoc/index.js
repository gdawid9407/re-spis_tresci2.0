import { registerBlockType } from '@wordpress/blocks';
import edit from './edit';
import save from './save';
import './editor.css';
import './style.css';

// Rejestracja bloku Toc
registerBlockType( 're-spis-tresci/toc', {
    edit,
    save,
} );
