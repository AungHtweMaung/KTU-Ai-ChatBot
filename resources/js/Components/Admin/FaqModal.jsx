import { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import Modal from './Modal';

export default function FaqModal({ show, faq, onClose }) {
    const isEdit = Boolean(faq);

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        category: '',
        question: '',
        answer: '',
        keywords: '',
        sort_order: 0,
        is_published: true,
    });

    useEffect(() => {
        if (show) {
            setData({
                category: faq?.category ?? '',
                question: faq?.question ?? '',
                answer: faq?.answer ?? '',
                keywords: faq?.keywords ?? '',
                sort_order: faq?.sort_order ?? 0,
                is_published: faq?.is_published ?? true,
            });
            clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [show, faq]);

    const handleSubmit = (e) => {
        e.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onClose();
            },
        };

        if (isEdit) {
            put(route('admin.faqs.update', faq.id), options);
        } else {
            post(route('admin.faqs.store'), options);
        }
    };

    const fieldStyle = {
        backgroundColor: 'var(--bs-body-bg)',
        color: 'var(--bs-body-color)',
        borderColor: 'var(--bs-border-color)',
    };

    return (
        <Modal
            show={show}
            onClose={onClose}
            title={isEdit ? 'Edit FAQ' : 'Add FAQ'}
            size="modal-lg"
        >
            <form onSubmit={handleSubmit}>
                <div className="row g-3">
                    <div className="col-md-8">
                        <label htmlFor="category" className="form-label">
                            Category
                        </label>
                        <input
                            id="category"
                            type="text"
                            className={`form-control ${errors.category ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            placeholder="e.g. Admissions, Fees, Timetable"
                            value={data.category}
                            onChange={(e) => setData('category', e.target.value)}
                        />
                        {errors.category && <div className="invalid-feedback">{errors.category}</div>}
                    </div>

                    <div className="col-md-4">
                        <label htmlFor="sort_order" className="form-label">
                            Sort Order
                        </label>
                        <input
                            id="sort_order"
                            type="number"
                            min="0"
                            className={`form-control ${errors.sort_order ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.sort_order}
                            onChange={(e) => setData('sort_order', e.target.value)}
                        />
                        {errors.sort_order && (
                            <div className="invalid-feedback">{errors.sort_order}</div>
                        )}
                    </div>

                    <div className="col-12">
                        <label htmlFor="question" className="form-label">
                            Question
                        </label>
                        <textarea
                            id="question"
                            rows={2}
                            className={`form-control ${errors.question ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.question}
                            onChange={(e) => setData('question', e.target.value)}
                        />
                        {errors.question && (
                            <div className="invalid-feedback">{errors.question}</div>
                        )}
                    </div>

                    <div className="col-12">
                        <label htmlFor="answer" className="form-label">
                            Answer
                        </label>
                        <textarea
                            id="answer"
                            rows={5}
                            className={`form-control ${errors.answer ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            value={data.answer}
                            onChange={(e) => setData('answer', e.target.value)}
                        />
                        {errors.answer && <div className="invalid-feedback">{errors.answer}</div>}
                    </div>

                    <div className="col-12">
                        <label htmlFor="keywords" className="form-label">
                            Keywords / Synonyms{' '}
                            <span className="text-muted fw-normal">(optional)</span>
                        </label>
                        <input
                            id="keywords"
                            type="text"
                            className={`form-control ${errors.keywords ? 'is-invalid' : ''}`}
                            style={fieldStyle}
                            placeholder="e.g. hostel, dormitory, အဆောင်, ကျောင်းဆောင်, အိပ်ဆောင်"
                            value={data.keywords}
                            onChange={(e) => setData('keywords', e.target.value)}
                        />
                        {errors.keywords ? (
                            <div className="invalid-feedback">{errors.keywords}</div>
                        ) : (
                            <small className="text-muted">
                                Comma-separated alternative words (any language) the chatbot
                                should also match for this question.
                            </small>
                        )}
                    </div>

                    <div className="col-12">
                        <div className="form-check">
                            <input
                                id="is_published"
                                type="checkbox"
                                className="form-check-input"
                                checked={data.is_published}
                                onChange={(e) => setData('is_published', e.target.checked)}
                            />
                            <label htmlFor="is_published" className="form-check-label">
                                Published (visible to students & chatbot)
                            </label>
                        </div>
                    </div>
                </div>

                <div className="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" className="btn btn-outline-secondary" onClick={onClose}>
                        Cancel
                    </button>
                    <button type="submit" className="btn btn-primary" disabled={processing}>
                        {isEdit ? 'Update' : 'Save'}
                    </button>
                </div>
            </form>
        </Modal>
    );
}
