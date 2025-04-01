import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { Project } from '@/types/model-types';
import { Head, Link, useForm } from '@inertiajs/react';

interface EditProjectProps {
    project: Project;
}

type EditProjectForm = {
    name: string;
    budget: number;
    start_date: Date;
    target_completion_date: Date;
};

export default function Edit(props: EditProjectProps) {
    const { project } = props;

    const { data, setData, errors, put } = useForm<Partial<EditProjectForm>>({
        name: project.name,
        budget: project.budget,
        start_date: project.start_date,
        target_completion_date: project.target_completion_date,
    });

    const handleChange = (e) => {
        setData({ ...data, [e.target.name]: e.target.value });
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('projects.update', project.id), {});
    };

    return (
        <AppLayout>
            <Head title={`Edit ${project.name}`} />
            <div className="bg-[var(--background)] p-6">
                <h2 className="mb-4 text-lg font-semibold">Edit Project</h2>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <Label htmlFor="name">Name</Label>
                        <Input type="text" id="name" name="name" value={data.name} onChange={handleChange} />
                        {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
                    </div>
                    <div>
                        <Label htmlFor="budget">Budget</Label>
                        <Input type="number" id="budget" name="budget" value={data.budget} onChange={handleChange} />
                        {errors.budget && <p className="text-sm text-red-500">{errors.budget}</p>}
                    </div>
                    <div>
                        <Label htmlFor="start_date">Start Date</Label>
                        <Input type="date" id="start_date" name="start_date" value={data.start_date} onChange={handleChange} />
                        {errors.start_date && <p className="text-sm text-red-500">{errors.start_date}</p>}
                    </div>
                    <div>
                        <Label htmlFor="target_completion_date">Target Completion Date</Label>
                        <Input
                            type="date"
                            id="target_completion_date"
                            name="target_completion_date"
                            value={data.target_completion_date}
                            onChange={handleChange}
                        />
                        {errors.target_completion_date && <p className="text-sm text-red-500">{errors.target_completion_date}</p>}
                    </div>
                    <div className="flex items-center justify-end space-x-2">
                        <Link href={route('projects.index')}>
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit">Update</Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
