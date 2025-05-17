import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Product, UserRating } from '@/types/model-types';
import { Textarea } from '@headlessui/react';
import { useForm } from '@inertiajs/react';
import { Star } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

interface ProductRatingProps {
    product: Product;
    userRating: UserRating;
}

export const ProductRating = (props: ProductRatingProps) => {
    const { product, userRating } = props;

    const {
        data: ratingData,
        setData: setRatingData,
        post: postRating,
        processing: processingRating,
        errors: ratingErrors,
        reset: resetRating,
    } = useForm({
        rating: userRating?.rating || 0,
        comment: userRating?.comment || '',
    });

    const [hoverRating, setHoverRating] = useState(0);

    const handleRatingSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        if (ratingData.rating > 0) {
            postRating(route('products.ratings.store', product.id), {
                preserveScroll: true,
                onSuccess: () => {
                    resetRating('comment');
                },
                onError: (errs) => {
                    console.error('Rating submission error:', errs);
                },
            });
        } else {
            console.error('Please select a star rating.');
        }
    };

    return (
        <Card className="shadow-md">
            <CardHeader>
                <CardTitle className="text-lg">Rate this product</CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleRatingSubmit} className="space-y-4">
                    <div>
                        <Label htmlFor="rating" className="mb-1 block text-sm font-medium">
                            Your Rating
                        </Label>
                        <div className="flex items-center">
                            {[1, 2, 3, 4, 5].map((star) => (
                                <Star
                                    key={star}
                                    className={`cursor-pointer transition-colors ${(hoverRating || ratingData.rating) >= star ? 'text-yellow-400 hover:text-yellow-500' : 'text-gray-300 hover:text-gray-400'}`}
                                    fill={(hoverRating || ratingData.rating) >= star ? 'currentColor' : 'none'}
                                    size={28}
                                    onClick={() => setRatingData('rating', star)}
                                    onMouseEnter={() => setHoverRating(star)}
                                    onMouseLeave={() => setHoverRating(0)}
                                    aria-label={`Rate ${star} out of 5 stars`}
                                />
                            ))}
                        </div>
                        {ratingErrors.rating && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{ratingErrors.rating}</p>}
                    </div>
                    <div>
                        <Label htmlFor="comment" className="text-sm font-medium">
                            Your Comment (Optional)
                        </Label>
                        <Textarea
                            id="comment"
                            value={ratingData.comment}
                            onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setRatingData('comment', e.target.value)}
                            className="border-input focus:border-primary focus:ring-primary mt-1 block w-full rounded-md border p-2 shadow-sm sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-50"
                            rows={4}
                        />
                        {ratingErrors.comment && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{ratingErrors.comment}</p>}
                    </div>
                    <Button type="submit" disabled={processingRating || ratingData.rating === 0}>
                        {processingRating ? 'Submitting...' : 'Submit Rating'}
                    </Button>
                </form>
            </CardContent>
        </Card>
    );
};
