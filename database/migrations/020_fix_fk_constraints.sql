-- Migration 020: Fix FK constraints on payments and payment_requests
-- Change fk_payments_member from RESTRICT to CASCADE
-- Change fk_payment_requests_member from SET NULL to CASCADE
-- This ensures that when a member is deleted, all related records cascade-delete cleanly.
--
-- Order matters: payments must be deleted before payment_requests
-- because payments.fk_payments_request is RESTRICT. MySQL handles
-- cascade order correctly: payments (referencing members) will be
-- deleted before payment_requests (also referencing members).

ALTER TABLE payments
    DROP FOREIGN KEY fk_payments_member;

ALTER TABLE payments
    ADD CONSTRAINT fk_payments_member
        FOREIGN KEY (member_id) REFERENCES members (id)
            ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE payment_requests
    DROP FOREIGN KEY fk_payment_requests_member;

ALTER TABLE payment_requests
    ADD CONSTRAINT fk_payment_requests_member
        FOREIGN KEY (member_id) REFERENCES members (id)
            ON DELETE CASCADE ON UPDATE CASCADE;
