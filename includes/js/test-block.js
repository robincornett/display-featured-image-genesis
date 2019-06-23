/*
 * Copyright (c) 2019 Robin Cornett
 */

(function ( wp, undefined ) {
	'use strict';
	const DFIGBlockObject = {
		el: wp.element.createElement,
	};

	/**
	 * Initialize and register the block.
	 */
	DFIGBlockObject.init = function ( params ) {
		const registerBlockType = wp.blocks.registerBlockType,
		      ServerSideRender  = wp.components.ServerSideRender,
		      InspectorControls = wp.editor.InspectorControls;

		registerBlockType( params.block, {
			title: params.title,
			description: params.description,
			keywords: params.keywords,
			icon: params.icon,
			category: params.category,
			supports: {
				html: false
			},

			getEditWrapperProps( {blockAlignment} ) {
				return {'data-align': blockAlignment};
			},

			edit: props => {
				const {
					      attributes,
					      setAttributes
				      }                     = props,
				      Fragment              = wp.element.Fragment,
				      BlockControls         = wp.editor.BlockControls,
				      BlockAlignmentToolbar = wp.editor.BlockAlignmentToolbar;
				return [
					DFIGBlockObject.el( ServerSideRender, {
						block: DFIGBlockObject.params.block,
						attributes: attributes
					} ),
					DFIGBlockObject.el( Fragment, null,
						DFIGBlockObject.el( BlockControls, null,
							DFIGBlockObject.el( BlockAlignmentToolbar, {
								value: attributes.blockAlignment,
								controls: ['wide', 'full'],
								onChange: ( value ) => {
									setAttributes( {blockAlignment: value} );
								},
							} )
						),
					),
					DFIGBlockObject.el( InspectorControls, {},
						_getPanels( props )
					)
				];
			},

			save: props => {
				return null;
			},
		} );
	};

	/**
	 * Get the panels for the block controls.
	 *
	 * @param props
	 * @return {Array}
	 * @private
	 */
	function _getPanels( props ) {
		const panels    = [],
		      PanelBody = wp.components.PanelBody;
		Object.keys( DFIGBlockObject.params.panels ).forEach( function ( key, index ) {
			if ( DFIGBlockObject.params.panels.hasOwnProperty( key ) ) {
				const IndividualPanel = DFIGBlockObject.params.panels[key];
				panels[index] = DFIGBlockObject.el( PanelBody, {
					title: IndividualPanel.title,
					initialOpen: IndividualPanel.initialOpen,
					className: 'scriptless-panel-' + key
				}, _getControls( props, IndividualPanel.attributes ) );
			}
		} );

		return panels;
	}

	/**
	 * Get all of the block controls, with defaults and options.
	 *
	 * @param props
	 * @param fields
	 * @return {Array}
	 * @private
	 */
	function _getControls( props, fields ) {
		const controls = [];
		Object.keys( fields ).forEach( function ( key, index ) {
			if ( fields.hasOwnProperty( key ) ) {
				var skipped = [ 'blockAlignment', 'className' ];
				if ( -1 !== skipped.indexOf( key ) ) {
					return;
				}
				const IndividualField = fields[key],
				      control         = _getControlType( IndividualField.method, IndividualField.type );
				controls[index] = DFIGBlockObject.el( control, _getIndividualControl( key, IndividualField, props ) );
			}
		} );

		return controls;
	}

	/**
	 * Get the control type.
	 * @param method
	 * @param control_type
	 * @return {*}
	 * @private
	 */
	function _getControlType( method, control_type ) {
		const {
			      TextControl,
			      SelectControl,
			      RangeControl,
			      CheckboxControl,
			      TextareaControl
		      } = wp.components;
		const control = TextControl;
		if ( 'select' === method ) {
			return SelectControl;
		} else if ( 'number' === method && 'number' === control_type ) {
			return RangeControl;
		} else if ( 'checkbox' === method ) {
			return CheckboxControl;
		} else if ( 'textarea' === method ) {
			return TextareaControl;
		}

		return control;
	}

	/**
	 * Build the individual control object. Sets up standard properties for all
	 * controls; then adds custom properties as needed.
	 *
	 * @param key
	 * @param field
	 * @param props
	 * @return {{label: *, value: *, className: string, onChange: onChange}}
	 * @private
	 */
	function _getIndividualControl( key, field, props ) {
		const {attributes, setAttributes} = props;
		const control = {
			label: field.label,
			value: attributes[key],
			className: 'displayfeaturedimagegenesis-' + key,
			onChange: ( value ) => {
				setAttributes( {[key]: value} );
			}
		};

		if ( 'select' === field.method ) {
			control.options = field.options;
		} else if ( 'number' === field.method ) {
			control.min = field.min;
			control.max = field.max;
			if ( 'number' !== field.type ) {
				control.type = 'number';
			} else {
				control.initialPosition = field.min;
			}
		} else if ( 'checkbox' === field.method ) {
			control.checked = attributes[key];
		}

		return control;
	}

	DFIGBlockObject.params = typeof DisplayFeaturedImageTestBlock === 'undefined' ? '' : DisplayFeaturedImageTestBlock;

	if ( typeof DFIGBlockObject.params !== 'undefined' ) {
		DFIGBlockObject.init( DFIGBlockObject.params );
	}
} )( wp );
