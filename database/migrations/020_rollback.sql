-- Rollback 020: Restore original FK constraints

ALTER TABLE payments
    DROP FOREIGN KEY fk_payments_member;

ALTER TABLE payments
    ADD CONSTRAINT fk_payments_member
        FOREIGN KEY (member_id) REFERENCES members (id)
            ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE payment_requests
    DROP FOREIGN KEY fk_payment_requests_member;

ALTER TABLE payment_requests
    ADD CONSTRAINT fk_payment_requests_member
        FOREIGN KEY (member_id) REFERENCES members (id)
            ON DELETE SET NULL ON UPDATE CASCADE;
