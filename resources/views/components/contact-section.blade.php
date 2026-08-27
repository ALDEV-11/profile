<section class="contact-section" id="contact">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">GET IN TOUCH</h2>
                <span class="section-divider"></span>
                <p class="section-subtitle">Feel free to contact me</p>
            </div>

            <div class="contact-grid">
                <div>
                    <h3 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 1.25rem; text-transform: uppercase;" class="text-gradient">Let's Talk About Everything</h3>
                    <p style="font-weight: 700; margin-bottom: 2rem; background: var(--bg-surface); padding: 1.25rem; border: var(--border-width) solid var(--border-color); box-shadow: 4px 4px 0px var(--shadow-color);">Don't be shy, say hi! I'm always open to new ideas and opportunities. Feel free to reach out if you have any questions, suggestions, or just want to chat!</p>

                    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <div style="display: flex; align-items: center; gap: 1.25rem; padding: 1rem; background: var(--neo-cyan); border: var(--border-width) solid var(--border-color); box-shadow: 4px 4px 0px var(--shadow-color);">
                            <div class="social-icon-btn" style="box-shadow: none;"><i class="bx bx-envelope"></i></div>
                            <div>
                                <div style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; color: #000000;">EMAIL</div>
                                <div style="font-weight: 800; color: #000000;">{{ $email }}</div>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 1.25rem; padding: 1rem; background: var(--neo-pink); border: var(--border-width) solid var(--border-color); box-shadow: 4px 4px 0px var(--shadow-color);">
                            <div class="social-icon-btn" style="box-shadow: none;"><i class="bx bx-phone"></i></div>
                            <div>
                                <div style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; color: #000000;">PHONE</div>
                                <div style="font-weight: 800; color: #000000;">{{ $phone }}</div>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 1.25rem; padding: 1rem; background: var(--neo-teal); border: var(--border-width) solid var(--border-color); box-shadow: 4px 4px 0px var(--shadow-color);">
                            <div class="social-icon-btn" style="box-shadow: none;"><i class="bx bx-map"></i></div>
                            <div>
                                <div style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; color: #000000;">LOCATION</div>
                                <div style="font-weight: 800; color: #000000;">{{ $address }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <x-contact-form />
            </div>
        </div>
    </section>