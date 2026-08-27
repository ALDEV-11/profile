<div class="contact-form-card">
    <form action="{{ route('contact.store') }}" method="POST" class="contact-form" id="contactForm" novalidate>
        @csrf

        <div class="form-group">
            <label class="form-label" for="contact_name">Your Name</label>
            <input
                type="text"
                id="contact_name"
                name="name"
                class="form-control"
                placeholder="John Doe"
                minlength="2"
                maxlength="100"
                required
            />
            <span class="field-error" data-error-for="name" role="alert"></span>
        </div>

        <div class="form-group">
            <label class="form-label" for="contact_email">Email Address</label>
            <input
                type="email"
                id="contact_email"
                name="email"
                class="form-control"
                placeholder="john@example.com"
                maxlength="255"
                required
            />
            <span class="field-error" data-error-for="email" role="alert"></span>
        </div>

        <div class="form-group">
            <label class="form-label" for="contact_subject">Subject</label>
            <input
                type="text"
                id="contact_subject"
                name="subject"
                class="form-control"
                placeholder="What's this about?"
                minlength="5"
                maxlength="100"
                required
            />
            <span class="field-error" data-error-for="subject" role="alert"></span>
        </div>

        <div class="form-group">
            <label class="form-label" for="contact_message">Message</label>
            <textarea
                id="contact_message"
                name="message"
                class="form-control"
                placeholder="Tell me something..."
                rows="5"
                minlength="10"
                maxlength="1000"
                required
            ></textarea>
            <span class="field-error" data-error-for="message" role="alert"></span>
        </div>

        <button type="submit" class="btn btn-primary contact-submit-btn" style="width: 100%;">
            <span class="btn-label">
                Send Message <i class="bx bx-paper-plane"></i>
            </span>
            <span class="btn-spinner" aria-hidden="true"></span>
        </button>
    </form>
</div>
