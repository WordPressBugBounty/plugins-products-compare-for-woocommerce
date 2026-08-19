import React, { useEffect, useMemo, useState } from 'react';

const { ModuleContainer, StyleContainer, elementClassnames } = window?.divi?.module || {};

const getPreviewConfig = () => {
  if (window?.BRCMPDivi5Preview) {
    return window.BRCMPDivi5Preview;
  }

  const scriptSrc = document?.currentScript?.src;
  if (!scriptSrc) {
    return {};
  }

  const params = new URL(scriptSrc).searchParams;
  return {
    ajaxUrl: decodeURIComponent(params.get('brcmp_ajax_url') || ''),
    action: params.get('brcmp_action') || '',
    nonce: params.get('brcmp_nonce') || '',
  };
};

const previewConfig = getPreviewConfig();

const Placeholder = ({ title, children }) => (
  <div style={{
    padding: '2em 0',
    background: '#6c2eb9',
    color: '#fff',
    fontSize: '12px',
    fontWeight: '600',
    textAlign: 'center',
    borderRadius: '1em',
  }}>
    <h3 style={{ color: '#000', fontWeight: '900' }}>{title}</h3>
    {children}
  </div>
);

const ComparePreview = ({ attrs, metadata }) => {
  const [state, setState] = useState({ html: '', isLoading: true, error: '' });
  const attrsKey = useMemo(() => JSON.stringify(attrs ?? {}), [attrs]);

  useEffect(() => {
    if (!previewConfig.ajaxUrl || !previewConfig.action || !previewConfig.nonce) {
      setState({ html: '', isLoading: false, error: `${metadata.title} is not displayed in Builder` });
      return undefined;
    }

    const controller = new AbortController();
    const body = new FormData();
    body.append('action', previewConfig.action);
    body.append('nonce', previewConfig.nonce);
    body.append('module_name', metadata.name);
    body.append('attrs', attrsKey);
    setState((current) => ({ ...current, isLoading: true, error: '' }));

    fetch(previewConfig.ajaxUrl, {
      body,
      method: 'POST',
      credentials: 'same-origin',
      signal: controller.signal,
    })
      .then((response) => response.json())
      .then((response) => {
        if (!response?.success) {
          throw new Error(response?.data?.message || `${metadata.title} is not displayed in Builder`);
        }
        setState({ html: response?.data?.html || '', isLoading: false, error: '' });
      })
      .catch((error) => {
        if (error.name !== 'AbortError') {
          setState({ html: '', isLoading: false, error: error.message || `${metadata.title} is not displayed in Builder` });
        }
      });

    return () => controller.abort();
  }, [attrsKey, metadata.name, metadata.title]);

  if (state.isLoading) {
    return <div className="et-fb-loader-wrapper"><div className="et-fb-loader" /></div>;
  }
  if (state.error || !state.html) {
    return <Placeholder title={metadata.title}>{state.error || `${metadata.title} has no content to display`}</Placeholder>;
  }
  return <div dangerouslySetInnerHTML={{ __html: state.html }} />;
};

export const createCompareModule = (metadata, styleAttrs) => {
  const ModuleStyles = ({ elements, settings, mode, state, noStyleTag }) => (
    <StyleContainer mode={mode} state={state} noStyleTag={noStyleTag}>
      {elements.style({
        attrName: 'module',
        styleProps: { disabledOn: { disabledModuleVisibility: settings?.disabledModuleVisibility } },
      })}
      {styleAttrs.map((attrName) => (
        <React.Fragment key={attrName}>{elements.style({ attrName })}</React.Fragment>
      ))}
    </StyleContainer>
  );

  const ModuleScriptData = ({ elements }) => (
    <React.Fragment>{elements.scriptData({ attrName: 'module' })}</React.Fragment>
  );

  const moduleClassnames = ({ classnamesInstance, attrs }) => {
    classnamesInstance.add(elementClassnames({ attrs: attrs?.module?.decoration ?? {} }));
  };

  const callbacks = metadata.name === 'brcmp/compare-button'
    ? {
      content: {
        contentOptions: {
          fields: {
            contentAdvancedProduct: {
              customFieldComponentName: 'divi/woocommerce-product-select',
            },
          },
        },
      },
    }
    : undefined;

  return {
    metadata,
    callbacks,
    renderers: {
      edit: ({ attrs, id, name, elements }) => (
        <ModuleContainer
          attrs={attrs}
          elements={elements}
          id={id}
          moduleClassName={metadata.moduleClassName}
          name={name}
          scriptDataComponent={ModuleScriptData}
          stylesComponent={ModuleStyles}
          classnamesFunction={moduleClassnames}
        >
          {elements.styleComponents({ attrName: 'module' })}
          {styleAttrs.map((attrName) => (
            <React.Fragment key={attrName}>{elements.styleComponents({ attrName })}</React.Fragment>
          ))}
          <div className="et_pb_module_inner"><ComparePreview attrs={attrs} metadata={metadata} /></div>
        </ModuleContainer>
      ),
    },
    placeholderContent: {
      module: { meta: { adminLabel: { desktop: { value: metadata.title } } } },
      ...metadata.defaultAttrs,
    },
  };
};
