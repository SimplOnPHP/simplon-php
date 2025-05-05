<?php
/*
Sow Peace License (MIT-Compatible with Attribution Visit)
Copyright (c) 2025 Ruben Schaffer Levine and Luca Lauretta
https://simplonphp.org/Sow-PeaceLicense.txt
*/

class SI_Modal extends SI_Link {
    
    protected $href, $text;
    
    function __construct($href, $content, $icon = null) {
        $this->href = $href;
        $this->content = $content;
        $this->icon = $icon;

        $this->addStylesToAutoCSS('
            .modal-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: var(--modal-overlay-background-color, rgba(0, 0, 0, 0.5));
            }

            .modal-content {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background-color: var(--card-background-color);
                color: var(--card-text-color);
                padding: var(--spacing);
                border-radius: var(--border-radius);
                border: var(--border-width) solid var(--card-border-color);
                max-width: 500px;
                width: 90%;
                box-shadow: var(--card-box-shadow);
            }

            .loading {
                text-align: center;
                padding: var(--spacing);
                color: var(--muted-color);
            }

            .close-button {
                position: absolute;
                right: var(--spacing);
                top: var(--spacing);
                cursor: pointer;
                font-size: 1.5rem;
                color: var(--muted-color);
                background: none;
                border: none;
                padding: 0;
                width: auto;
                margin: 0;
            }

            .close-button:hover {
                color: var(--primary);
            }

            /* Styles for stacked modals */
            .modal-stack {
                position: relative;
            }
        ');
    }

    function setTagsVals($renderVals = null) {
        $onclick =  "Modal.open('{$renderVals['href']}')";
        if($renderVals['icon'] AND is_string($renderVals['content'])){
            $image = new SI_Image($renderVals['icon'],$renderVals['content'].'');
            $image->addClass('icon');
            $renderVals['content'] = '';
        }
        //$this->start = '<a '.$onclick.' class="SI_Link" href="#">';
        $this->start = new SI_Link('#', $image);
        $this->start->addAttribute('onClick',$onclick);
        $this->start->addClass('SI_Link');
        $this->end = "</a>\n";


        $this->end .= new SI_Script(<<<MODAL_JS
        const Modal = {
            loadedContents: new Map(),
            modalStack: [],
            baseZIndex: 1000,
            
            createModal: function(id) {
                return \$(`
                    <div id="\${id}" class="modal-overlay">
                        <div class="modal-content">
                            <button class="close-button" aria-label="Close">&times;</button>
                            <div class="modal-body">
                                <div class="loading">Loading...</div>
                            </div>
                        </div>
                    </div>
                `);
            },
        
            open: function(url) {
                const modalId = 'modal-' + Date.now();
                const \$modal = this.createModal(modalId);
                
                // Calculate z-index for new modal
                const zIndex = this.baseZIndex + (this.modalStack.length * 10);
                \$modal.css('z-index', zIndex);
                \$modal.find('.modal-content').css('z-index', zIndex + 1);
                
                // Add to DOM and show
                \$('body').append(\$modal);
                \$modal.fadeIn();
                
                // Setup event handlers
                this.setupEvents(\$modal);
                
                // Add to stack
                this.modalStack.push({
                    id: modalId,
                    url: url
                });
                
                // Load content
                if (this.loadedContents.has(url)) {
                    const content = this.loadedContents.get(url);
                    this.setModalContent(\$modal, content);
                } else {
                    this.loadContent(modalId, url);
                }
            },
        
            loadContent: function(modalId, url) {
                const \$modal = \$(`#\${modalId}`);
                
                \$.ajax({
                    url: url,
                    method: 'GET',
                    success: (response) => {
                        this.loadedContents.set(url, response);
                        this.setModalContent(\$modal, response);
                    },
                    error: () => {
                        \$modal.find('.modal-body').html('Error loading content.');
                    }
                });
            },
        
            setModalContent: function(\$modal, content) {
                const \$modalBody = \$modal.find('.modal-body');
                \$modalBody.html(content);
                
                // Initialize SimplOn within the modal context
                SimplOn.context = \$modalBody[0];
                SimplOn.initActions();
                SimplOn.initForms();
                SimplOn.paging();
                
                // Set focus on first input/button
                \$('input,button', \$modalBody).first().focus();
            },
        
            close: function(modalId) {
                const \$modal = \$(`#\${modalId}`);
                
                \$modal.fadeOut(() => {
                    \$modal.remove();
                });
                
                // Remove from stack
                this.modalStack = this.modalStack.filter(modal => modal.id !== modalId);
                
                // Reset SimplOn context to document after closing
                if (this.modalStack.length === 0) {
                    SimplOn.context = window.document;
                }
            },
        
            setupEvents: function(\$modal) {
                const modalId = \$modal.attr('id');
                
                // Close button handler
                \$modal.find('.close-button').click(() => {
                    this.close(modalId);
                });
        
                // Overlay click handler (separate from close button)
                \$modal.on('click', (e) => {
                    if (e.target === e.currentTarget) {
                        this.close(modalId);
                    }
                });
        
                // Prevent modal content clicks from bubbling to overlay
                \$modal.find('.modal-content').click((e) => {
                    e.stopPropagation();
                });
        
                // Handle escape key
                \$(document).keydown((e) => {
                    if (e.key === 'Escape') {
                        // Close only the top modal
                        if (this.modalStack.length > 0) {
                            const topModal = this.modalStack[this.modalStack.length - 1];
                            this.close(topModal.id);
                        }
                    }
                });
            }
        };
        MODAL_JS);

    }
}
